# phalcon-graphql

## Security migration (unreleased)

These changes require migration of using applications and a major release. The
package version has not yet been published or changed. Deploy application policies
and updated dependency lockfiles together with the library.

### Authorization context and plugin registration

Register `AclAuthorizationPlugin` once using `Schema::plugin()`. Registration on a
field, object, union, collection or group now throws a configuration exception.
Keep field/object grants and denies on their definitions.

Call `$dispatcher->setDI($requestDi)` explicitly. The ACL, user service, handlers,
resolvers and mutation model lookups use that container. Set up the schema ACL in
every execution container using `AclAuthorizationPlugin::mountSchema($schema,
$requestDi)` when it differs from the build container. Missing services deny
access; no global DI fallback is used for authorization. Resolver/handler caches
are cleared between dispatches. Authentication services must still reset their
identity between requests in the application/phalcon-api.

Existing plugins keep their `beforeResolve()` signature. Plugins that need the
execution DI implement `ExecutionPluginInterface::beforeResolveWithContext()`;
the dispatcher calls this method instead of `beforeResolve()`. Subclasses of the
ACL plugin that previously overrode `beforeResolve()` must migrate their custom
authorization to the new method and call its parent. `SchemaPluginInterface`
marks plugins that can only be registered on a schema.

Missing application field definitions now deny resolution. GraphQL metadata uses
its own native resolvers. Build the definition schema and executable schema
together; custom native GraphQL resolvers remain responsible for authorization.

### Explicit mutation inputs

Generated model inputs require `only()` with mapped property names, or explicit
`InputField` definitions. An unconfigured generated input fails during schema
building. New database columns are never added to inputs without an explicit
choice. Existing `exclude()` calls alone are insufficient.

Standard create/update mutations also require `writableFields()`. Its array lists
all accepted client input properties, including the primary key for updates.
Unlisted properties reject the entire mutation before assignment. Omission denies
standard create/update mutations. Reading an output field and writing an input
property are separate permissions.

```php
$schema->inputObject(ModelInputObjectType::create(Record::class)->only(['name']));
$schema->inputObject(ModelInputObjectType::update(Record::class)->only(['id', 'name']));

$mutation->field((new CreateModelField(Record::class))->writableFields(['name']));
$mutation->field((new UpdateModelField(Record::class))->writableFields(['id', 'name']));
```

A request-specific callback can return the allowed properties for a role. It is
called with `($item, $data, $args, $field, $requestDi)`; `$item` is null for create.
Return `[]` to reject nonempty input, or null to reject all input.

```php
$field->writableFields(function ($item, $data, $args, $field, $requestDi) {
    return $requestDi->get('userService')->getRole() === 'admin'
        ? ['id', 'name', 'isAdmin']
        : ['id', 'name'];
});
```

Assign tenant/owner values server-side in assignment hooks. Input policies inspect
client data before server-side transformations; review those transformations and
custom mutation implementations separately. Handlers/resolvers can override
`_inputAllowed($item, array $data, array $args, Field $field)` for application
policies, preferably retaining the parent whitelist check.

### Record and tenant authorization

The library cannot infer ownership or tenant rules. Field ACL and writable input
lists do not grant record access. Applications must implement `_createAllowed()`,
`_updateAllowed()`, `_deleteAllowed()`, `_findAllowed()`, `_allAllowed()` and
`_relationAllowed()` as applicable. Their default record behavior remains
permissive. For example, an application handler can enforce ownership:

```php
protected function _updateAllowed($item, array $data, array $args, Field $field)
{
    return $item->ownerId == $this->getDI()->get('currentOwnerId');
}

protected function _deleteAllowed($item, array $args, Field $field)
{
    return $item->ownerId == $this->getDI()->get('currentOwnerId');
}
```

Apply the same policy to reads, lists, relations and counts using bound query
restrictions in `_modifyQuery()`, `_modifyAllQuery()`, `_modifyFindQuery()` and
`_modifyRelationOptions()`, as applicable. Relation counts also need a scoped
implementation; the default relation count is not tenant-aware. Scope mutation
lookups when existence itself is confidential. Verify with at least two owners
and tenants, including counts and related records. The regression suite contains
an ownership handler example for update/delete; it does not certify application
policies.

### Model keys and errors

Standard mutations support one primary key, including string keys and column
maps. Every lookup binds the ID as a value. Numeric keys reject nonnumeric strings
before execution, avoiding database coercion. Missing/composite keys or unsupported
mapped key identifiers fail closed; implement a separate bound lookup and result
handling for models requiring other key forms. Create validates key support
before database writes.

Standard mutation errors expose fixed messages and codes, without input, full
models or raw model validation messages. `Exception::getUserInfo()` contains only
the code and category. Constructor extras are available through
`getDiagnosticInfo()` for internal handling and are not serialized by the GraphQL
or HTTP error formatters. Redact diagnostics before logging. Custom public
validation errors must use explicitly reviewed messages; exception messages are
still client-visible. Never enable debug output for untrusted production users.

### Dependencies and tests

The minimum `webonyx/graphql-php` version is now `15.32.3`, covering the
[parser recursion advisory](https://github.com/webonyx/graphql-php/security/advisories/GHSA-r7cg-qjjm-xhqq)
and preceding validation advisories. Audit each application's actual lockfile.
The obsolete direct JWT v5 suggestion was removed; JWT configuration and
authentication fixes belong to phalcon-api and still require separate work.

With PHP 8.4+, Phalcon and PDO SQLite installed, resolve the current dependencies
and run the isolated in-memory regression suite:

```sh
composer update
composer test:security
composer audit --locked
```

`composer.lock` is intentionally ignored by this library. Old local vendor/lock
files must be refreshed. Tests can use another Composer runtime through
`GRAPHQL_TEST_AUTOLOAD=/absolute/path/to/vendor/autoload.php`. The tests use only
temporary SQLite databases and make no external application changes.

Validated on PHP 8.4.26 / Phalcon 5.22.1 with graphql-php 15.32.3 and 15.38.1:
56 regression checks per version, PHP syntax checks and isolated dependency
audits. Applications must validate their own supported runtime combinations.

Filter/sort/search policies, union ACL semantics, complete schema immutability,
introspection policy and application authentication rollout remain separate
mitigations in `SECURITY-MITIGATION-PLAN.md`.
