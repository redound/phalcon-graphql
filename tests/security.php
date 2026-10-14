<?php

use GraphQL\Utils\BuildSchema;
use Phalcon\Di\Di;
use PhalconGraphQL\Definition\Collections\Collection;
use PhalconGraphQL\Definition\FieldGroups\FieldGroup;
use PhalconGraphQL\Definition\Fields\CreateModelField;
use PhalconGraphQL\Definition\Fields\DeleteModelField;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\Fields\UpdateModelField;
use PhalconGraphQL\Definition\InputField;
use PhalconGraphQL\Definition\InputObjectType;
use PhalconGraphQL\Definition\ModelInputObjectType;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\ObjectTypeGroups\ObjectTypeGroup;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Definition\UnionType;
use PhalconGraphQL\Dispatcher;
use PhalconGraphQL\GraphQL\DocumentFactory;
use PhalconGraphQL\Plugins\Authorization\AclAuthorizationPlugin;

require getenv('GRAPHQL_TEST_AUTOLOAD') ?: dirname(__DIR__) . '/vendor/autoload.php';

class SecurityUserService extends PhalconApi\User\Service
{
    public function __construct(public string $role = 'member')
    {
    }

    public function getRole()
    {
        return $this->role;
    }
}

class SecurityRecord extends Phalcon\Mvc\Model
{
    public $id;
    public $ownerId;
    public $name;
    public $secret;
    public $isAdmin;

    public function initialize()
    {
        $this->setSource('records');
    }

    public function beforeCreate()
    {
        return $this->validateName();
    }

    public function beforeUpdate()
    {
        return $this->validateName();
    }

    private function validateName()
    {
        if ($this->name === 'invalid') {
            $this->appendMessage(new Phalcon\Messages\Message('Sensitive validation: private-secret token-value'));
            return false;
        }

        return true;
    }

    public function beforeDelete()
    {
        if ($this->name === 'undeletable') {
            $this->appendMessage(new Phalcon\Messages\Message('Sensitive delete: private-secret'));
            return false;
        }

        return true;
    }
}

class SecurityMappedRecord extends Phalcon\Mvc\Model
{
    public $key;
    public $name;

    public function initialize()
    {
        $this->setSource('mapped_records');
    }

    public function columnMap()
    {
        return ['record_key' => 'key', 'name' => 'name'];
    }
}

class SecurityCompositeRecord extends Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->setSource('composite_records');
    }
}

class SecurityKeylessRecord extends Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->setSource('keyless_records');
    }
}

class SecurityOwnedHandler extends PhalconGraphQL\Handlers\ModelMutationHandler
{
    protected function _updateAllowed($item, array $data, array $args, Field $field)
    {
        return $item->ownerId == $this->getDI()->get('ownerId');
    }

    protected function _deleteAllowed($item, array $args, Field $field)
    {
        return $item->ownerId == $this->getDI()->get('ownerId');
    }
}

class SecurityContextResolver extends PhalconGraphQL\Resolvers\Resolver
{
    public function resolve($source, array $args, Field $field)
    {
        return $this->getDI()->get('userService')->getRole();
    }
}

function securityDi(string $role = 'member')
{
    $di = new PhalconGraphQL\Di\FactoryDefault();
    foreach (['member', 'admin', 'guest'] as $name) {
        $di->get('acl')->addRole($name);
    }
    $di->setShared('userService', new SecurityUserService($role));
    $di->setShared('ownerId', fn () => 101);
    $di->setShared('db', new Phalcon\Db\Adapter\Pdo\Sqlite(['dbname' => ':memory:']));
    $db = $di->get('db');
    $db->execute('CREATE TABLE records (id INTEGER PRIMARY KEY AUTOINCREMENT, ownerId INTEGER, name TEXT, secret TEXT, isAdmin INTEGER)');
    $db->execute("INSERT INTO records VALUES (1, 101, 'owned', 'private-secret', 0), (2, 202, 'foreign', 'private-secret', 0), (3, 101, 'undeletable', 'private-secret', 0)");
    $db->execute('CREATE TABLE mapped_records (record_key TEXT PRIMARY KEY, name TEXT)');
    $db->execute("INSERT INTO mapped_records VALUES ('uuid-one', 'one'), ('id = 2 OR 1 = 1', 'literal')");
    $db->execute('CREATE TABLE composite_records (first_key INTEGER, second_key INTEGER, PRIMARY KEY (first_key, second_key))');
    $db->execute('CREATE TABLE keyless_records (name TEXT)');
    Di::setDefault($di);

    return $di;
}

function securitySchema()
{
    $schema = new Schema();
    $schema->plugin(new AclAuthorizationPlugin());
    $schema->object(ObjectType::query()->field(Field::string('public')->allow('member')->resolver(fn () => 'public')));

    return $schema;
}

function securityExecutable(Schema $schema, $di)
{
    $schema->build($di);
    $graphql = null;
    $graphql = BuildSchema::build(DocumentFactory::build($schema), DocumentFactory::createTypeConfigDecorator($schema, function () use (&$graphql) {
        return $graphql;
    }));

    return $graphql;
}

function securityDispatch(Schema $schema, $graphql, $di, string $query, array $variables = [])
{
    $dispatcher = new Dispatcher();
    $dispatcher->setDI($di);

    return $dispatcher->dispatch($schema, $graphql, false, ['query' => $query, 'variables' => $variables]);
}

$checks = 0;
function verify(string $name, bool $condition)
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $name);
    }

    ++$checks;
    echo 'PASS: ', $name, "\n";
}

function verifyRejected(string $name, callable $action)
{
    try {
        $action();
    }
    catch (LogicException $e) {
        verify($name, true);
        return;
    }

    verify($name, false);
}

$di = securityDi();
$schema = securitySchema();
$schema->findObjectType('Query')
    ->field(Field::string('secret')->allow('admin')->deny('member')->resolver(fn () => 'private-secret'))
    ->field(Field::factory('record', 'Record')->allow('member')->resolver(fn () => ['name' => 'visible', 'secret' => 'private-secret']));
$schema->object(ObjectType::factory('Record')->allow('member')->denyField('secret', 'member')
    ->field(Field::string('name'))->field(Field::string('secret')));
$writes = 0;
$schema->object(ObjectType::mutation()->field(Field::string('blocked')->deny('member')->resolver(function () use (&$writes) {
    ++$writes;
    return 'written';
})));
$graphql = securityExecutable($schema, $di);
$r = securityDispatch($schema, $graphql, $di, '{ alias: secret record { name secret } }');
verify('ACL rejects root aliases and nested fields, permitted fields work', isset($r['errors']) && $r['data']['alias'] === null && $r['data']['record']['secret'] === null && $r['data']['record']['name'] === 'visible');
$r = securityDispatch($schema, $graphql, $di, 'query { record { ...Hidden } } fragment Hidden on Record { alias: secret }');
verify('ACL rejects fields inside named fragments', isset($r['errors']) && $r['data']['record']['alias'] === null);
$r = securityDispatch($schema, $graphql, $di, 'mutation { blocked }');
verify('Mutation ACL rejects before side effects', isset($r['errors']) && $writes === 0);
$di->get('userService')->role = 'guest';
$r = securityDispatch($schema, $graphql, $di, '{ public }');
verify('ACL denies ungranted roles', isset($r['errors']) && $r['data']['public'] === null);
$di->get('userService')->role = 'member';
$di->get('acl')->addRole('editor', 'member');
$di->get('userService')->role = 'editor';
$r = securityDispatch($schema, $graphql, $di, '{ public secret }');
verify('Inherited role retains grants and denies', ($r['errors'][0]['code'] ?? null) === 3010 && $r['data']['public'] === 'public' && $r['data']['secret'] === null);
$di->get('acl')->allow('admin', '*', '*');
$di->get('userService')->role = 'admin';
$r = securityDispatch($schema, $graphql, $di, '{ public secret }');
verify('Trusted wildcard ACL grants still work', !isset($r['errors']) && $r['data']['public'] === 'public' && $r['data']['secret'] === 'private-secret');
$di->get('userService')->role = 'member';
$r = securityDispatch($schema, $graphql, $di, '{ __typename __schema { queryType { name } } }');
verify('GraphQL metadata still resolves', !isset($r['errors']) && $r['data']['__typename'] === 'Query');
$schema->findObjectType('Record')->removeField('secret');
$r = securityDispatch($schema, $graphql, $di, '{ record { secret } }');
verify('Missing application definitions fail closed', isset($r['errors']) && $r['data']['record']['secret'] === null);

foreach ([Field::string('secret'), ObjectType::query(), Collection::factory(), FieldGroup::factory(), ObjectTypeGroup::factory(), UnionType::factory('Result')] as $scope) {
    verifyRejected('Reject local ACL registration on ' . get_class($scope), fn () => $scope->plugin(new AclAuthorizationPlugin()));
}
verifyRejected('ACL without execution context fails closed', fn () => (new AclAuthorizationPlugin())->beforeResolve($schema, ObjectType::query(), Field::string('secret')));
verifyRejected('Dispatcher without explicit DI fails closed', fn () => (new Dispatcher())->dispatch($schema, $graphql, false, ['query' => '{ public }']));
$legacyPlugin = new class extends PhalconGraphQL\Plugins\Plugin {
    public $calls = 0;

    public function beforeResolve(Schema $schema, ObjectType $objectType, Field $field)
    {
        ++$this->calls;
    }
};
$legacySchema = securitySchema()->plugin($legacyPlugin);
$legacyGraphql = securityExecutable($legacySchema, $di);
$r = securityDispatch($legacySchema, $legacyGraphql, $di, '{ first: public second: public }');
verify('Existing plugin beforeResolve signature remains supported', !isset($r['errors']) && $legacyPlugin->calls === 2);

$memberDi = securityDi('member');
$schema = securitySchema();
$schema->findObjectType('Query')->field(Field::string('secret')->allow('admin')->deny('member')->resolver(fn () => 'secret'))
    ->field(Field::string('role')->allow(['member', 'admin'])->resolver(SecurityContextResolver::class));
$graphql = securityExecutable($schema, $memberDi);
$adminDi = securityDi('admin');
AclAuthorizationPlugin::mountSchema($schema, $adminDi);
$r = securityDispatch($schema, $graphql, $memberDi, '{ secret role }');
verify('Global admin cannot authorize a member dispatch', isset($r['errors']) && $r['data']['secret'] === null && $r['data']['role'] === 'member');
Di::setDefault($memberDi);
$r = securityDispatch($schema, $graphql, $adminDi, '{ secret role }');
verify('Admin dispatch uses its own authorization and resolver DI', !isset($r['errors']) && $r['data']['secret'] === 'secret' && $r['data']['role'] === 'admin');
$dispatcher = new Dispatcher();
foreach ([$memberDi, $adminDi, $memberDi] as $contextDi) {
    $dispatcher->setDI($contextDi);
    $r = $dispatcher->dispatch($schema, $graphql, false, ['query' => '{ role }']);
    verify('Reused dispatcher keeps resolver context ' . $contextDi->get('userService')->role, $r['data']['role'] === $contextDi->get('userService')->role);
}
foreach (['userService', 'acl'] as $service) {
    $brokenDi = securityDi();
    AclAuthorizationPlugin::mountSchema($schema, $brokenDi);
    $brokenDi->remove($service);
    Di::setDefault($adminDi);
    $r = securityDispatch($schema, $graphql, $brokenDi, '{ role }');
    verify('Missing ' . $service . ' denies without global fallback', isset($r['errors']) && $r['data']['role'] === null);
}

$di = securityDi();
$schema = securitySchema();
$schema->object(ObjectType::factory('SecurityRecord')->allow('member')->denyField('secret', 'member')
    ->field(Field::id('id'))->field(Field::string('name'))->field(Field::string('secret'))->field(Field::int('isAdmin')));
$schema->inputObject(InputObjectType::factory('UpdateSecurityRecordInput')->field(InputField::id('id')->nonNull())
    ->field(InputField::string('name'))->field(InputField::int('isAdmin')));
$schema->inputObject(InputObjectType::factory('CreateSecurityRecordInput')->field(InputField::string('name'))->field(InputField::int('isAdmin')));
$schema->object(ObjectType::mutation()->allow('member')
    ->field((new CreateModelField(SecurityRecord::class))->writableFields(['name']))
    ->field((new UpdateModelField(SecurityRecord::class))->writableFields(['id', 'name']))
    ->field(new DeleteModelField(SecurityRecord::class)));
$graphql = securityExecutable($schema, $di);
foreach (['mutation { createSecurityRecord(input: {name: "invalid"}) { id } }', 'mutation { updateSecurityRecord(input: {id: "2", name: "invalid"}) { id } }', 'mutation { deleteSecurityRecord(id: "3") }'] as $query) {
    $r = securityDispatch($schema, $graphql, $di, $query);
    $json = json_encode($r);
    verify('Failed model mutation does not expose model, input or validation data', ($r['errors'][0]['code'] ?? null) === 4010 && !str_contains($json, 'private-secret') && !str_contains($json, 'token-value') && !isset($r['errors'][0]['userInfo']['item']) && !isset($r['errors'][0]['userInfo']['data']) && !isset($r['errors'][0]['userInfo']['messages']));
}
$before = $di->get('db')->fetchAll('SELECT * FROM records');
$r = securityDispatch($schema, $graphql, $di, 'mutation { createSecurityRecord(input: {name: "new", isAdmin: 1}) { id } }');
verify('Privileged create input rejects before database writes', ($r['errors'][0]['code'] ?? null) === 3010 && $before === $di->get('db')->fetchAll('SELECT * FROM records'));
foreach (['mutation { deleteSecurityRecord(id: "id = 2 OR 1 = 1") }', 'mutation { updateSecurityRecord(input: {id: "id = 2 OR 1 = 1", name: "changed"}) { id } }'] as $query) {
    $r = securityDispatch($schema, $graphql, $di, $query);
    verify('PHQL-like mutation ID cannot select or change records', isset($r['errors']) && $before === $di->get('db')->fetchAll('SELECT * FROM records'));
}
$r = securityDispatch($schema, $graphql, $di, 'mutation { updateSecurityRecord(input: {id: "1", isAdmin: 1}) { id } }');
verify('Non-writable input fails before assignment', isset($r['errors']) && $before === $di->get('db')->fetchAll('SELECT * FROM records'));
$r = securityDispatch($schema, $graphql, $di, 'mutation { updateSecurityRecord(input: {id: "1", name: "updated"}) { id name } }');
verify('Permitted numeric ID update works', !isset($r['errors']) && $r['data']['updateSecurityRecord']['name'] === 'updated');
$r = securityDispatch($schema, $graphql, $di, 'mutation { createSecurityRecord(input: {name: "created"}) { id name } }');
verify('Permitted create and safe response lookup work', !isset($r['errors']) && $r['data']['createSecurityRecord']['name'] === 'created');

$field = $schema->findObjectType('Mutation')->findField('updateSecurityRecord');
$field->writableFields(function ($item, $data, $args, $field, $contextDi) {
    return $contextDi->get('userService')->getRole() === 'admin' ? ['id', 'isAdmin'] : ['id', 'name'];
});
Di::setDefault($adminDi);
$r = securityDispatch($schema, $graphql, $di, 'mutation { updateSecurityRecord(input: {id: "1", isAdmin: 1}) { id } }');
verify('Input policy uses dispatch DI while global DI is admin', isset($r['errors']) && $di->get('db')->fetchOne('SELECT isAdmin FROM records WHERE id = 1')['isAdmin'] == 0);
$field->writableFields(['id', 'name'])->handler(SecurityOwnedHandler::class);
$deleteField = $schema->findObjectType('Mutation')->findField('deleteSecurityRecord')->handler(SecurityOwnedHandler::class);
foreach (['mutation { updateSecurityRecord(input: {id: "2", name: "changed"}) { id } }', 'mutation { deleteSecurityRecord(id: "2") }'] as $query) {
    $r = securityDispatch($schema, $graphql, $di, $query);
    verify('Project record policy rejects foreign records', ($r['errors'][0]['code'] ?? null) === 3010 && $di->get('db')->fetchOne('SELECT name FROM records WHERE id = 2')['name'] === 'foreign');
}
$r = securityDispatch($schema, $graphql, $di, 'mutation { deleteSecurityRecord(id: "1") }');
verify('Permitted owned delete works in dispatch database', !isset($r['errors']) && $r['data']['deleteSecurityRecord'] === true && $di->get('db')->fetchOne('SELECT COUNT(*) AS total FROM records WHERE id = 1')['total'] == 0 && $adminDi->get('db')->fetchOne('SELECT COUNT(*) AS total FROM records WHERE id = 1')['total'] == 1);
$schema->findObjectType('Mutation')->field((new UpdateModelField(SecurityRecord::class))->allow('member'));
$r = securityDispatch($schema, $graphql, $di, 'mutation { updateSecurityRecord(input: {id: "2", name: "changed"}) { id } }');
verify('Missing mutation input policy denies writes', ($r['errors'][0]['code'] ?? null) === 3010 && $di->get('db')->fetchOne('SELECT name FROM records WHERE id = 2')['name'] === 'foreign');
$schema->findObjectType('Mutation')->field((new CreateModelField(SecurityRecord::class))->allow('member'));
$before = $di->get('db')->fetchAll('SELECT * FROM records');
$r = securityDispatch($schema, $graphql, $di, 'mutation { createSecurityRecord(input: {}) { id } }');
verify('Missing create policy also rejects empty input', ($r['errors'][0]['code'] ?? null) === 3010 && $before === $di->get('db')->fetchAll('SELECT * FROM records'));

$di = securityDi();
$schema = securitySchema();
verifyRejected('Unrestricted generated input is rejected', fn () => ModelInputObjectType::update(SecurityRecord::class)->build($schema, $di));
$explicitInput = ModelInputObjectType::update(SecurityRecord::class)->field(InputField::id('id')->nonNull());
$explicitInput->build($schema, $di);
verify('Explicit model inputs do not append unconfigured columns', array_map(fn ($field) => $field->getName(), $explicitInput->getFields()) === ['id']);
$input = ModelInputObjectType::update(SecurityRecord::class)->only(['id', 'name']);
$schema->inputObject($input);
$schema->object(ObjectType::factory('SecurityRecord')->allow('member')->field(Field::id('id'))->field(Field::string('name')));
$schema->object(ObjectType::mutation()->field((new UpdateModelField(SecurityRecord::class))->allow('member')->writableFields(['id', 'name'])));
$graphql = securityExecutable($schema, $di);
verify('Generated input contains only explicitly allowed properties', array_map(fn ($field) => $field->getName(), $input->getFields()) === ['id', 'name']);
$r = securityDispatch($schema, $graphql, $di, 'mutation { updateSecurityRecord(input: {id: "2", isAdmin: 1}) { id } }');
verify('GraphQL rejects excluded privileged properties', isset($r['errors']) && $di->get('db')->fetchOne('SELECT isAdmin FROM records WHERE id = 2')['isAdmin'] == 0);
$r = securityDispatch($schema, $graphql, $di, 'mutation { updateSecurityRecord(input: {id: "1", name: "allowed"}) { id name } }');
verify('Explicit generated input remains usable', !isset($r['errors']) && $r['data']['updateSecurityRecord']['name'] === 'allowed');

$schema = securitySchema();
$schema->object(ObjectType::factory('SecurityMappedRecord')->allow('member')->field(Field::id('key'))->field(Field::string('name')));
$schema->inputObject(ModelInputObjectType::update(SecurityMappedRecord::class)->only(['key', 'name']));
$schema->inputObject(ModelInputObjectType::create(SecurityMappedRecord::class)->only(['key', 'name']));
$schema->object(ObjectType::mutation()->allow('member')
    ->field((new UpdateModelField(SecurityMappedRecord::class))->writableFields(['key', 'name']))
    ->field((new CreateModelField(SecurityMappedRecord::class))->writableFields(['key', 'name']))
    ->field(new DeleteModelField(SecurityMappedRecord::class)));
$graphql = securityExecutable($schema, $di);
$r = securityDispatch($schema, $graphql, $di, 'mutation { updateSecurityMappedRecord(input: {key: "uuid-one", name: "changed"}) { key name } }');
verify('Mapped string primary key update and response lookup work', !isset($r['errors']) && $r['data']['updateSecurityMappedRecord']['key'] === 'uuid-one' && $r['data']['updateSecurityMappedRecord']['name'] === 'changed');
$r = securityDispatch($schema, $graphql, $di, 'mutation { updateSecurityMappedRecord(input: {key: "id = 2 OR 1 = 1", name: "literal only"}) { key name } }');
verify('PHQL-like string primary key is treated as a literal', !isset($r['errors']) && $r['data']['updateSecurityMappedRecord']['name'] === 'literal only' && $di->get('db')->fetchOne("SELECT name FROM mapped_records WHERE record_key = 'uuid-one'")['name'] === 'changed');
$r = securityDispatch($schema, $graphql, $di, 'mutation { createSecurityMappedRecord(input: {key: "uuid-new", name: "new"}) { key } }');
verify('String primary key create works', !isset($r['errors']) && $r['data']['createSecurityMappedRecord']['key'] === 'uuid-new');
$r = securityDispatch($schema, $graphql, $di, 'mutation { deleteSecurityMappedRecord(id: "uuid-one") }');
verify('String primary key delete works', !isset($r['errors']) && $r['data']['deleteSecurityMappedRecord'] === true);
$probe = new class extends PhalconGraphQL\Resolvers\DeleteModelResolver {
    public function lookup($id, Field $field)
    {
        return $this->_findModel($id, $field);
    }
};
$probe->setDI($di);
foreach (['0 OR 1 = 1', '2 OR 1 = 1'] as $id) {
    try {
        $probe->lookup($id, new DeleteModelField(SecurityRecord::class));
        verify('Numeric model rejects coercible PHQL-like ID', false);
    }
    catch (PhalconGraphQL\Exception $e) {
        verify('Numeric model rejects coercible PHQL-like ID', $e->getCode() === PhalconApi\Constants\ErrorCodes::POST_DATA_INVALID);
    }
}
foreach ([SecurityCompositeRecord::class, SecurityKeylessRecord::class] as $modelClass) {
    verifyRejected('Unsupported primary key fails closed on ' . $modelClass, fn () => $probe->lookup('1', new DeleteModelField($modelClass)));
}

$e = new PhalconGraphQL\Exception(4010, 'Public message', null, ['code' => 'token-value', 'category' => 'private-secret', 'item' => ['secret' => 'private-secret'], 'data' => ['token' => 'token-value']]);
verify('Exception extras cannot override public code/category', $e->getUserInfo() === ['code' => 4010, 'category' => 'graphql']);
verify('Diagnostic extras remain explicitly accessible internally', $e->getDiagnosticInfo()['item']['secret'] === 'private-secret');
$schema = securitySchema();
$schema->findObjectType('Query')->field(Field::string('failure')->allow('member')->resolver(function () use ($e) {
    throw $e;
}));
$graphql = securityExecutable($schema, $di);
$r = securityDispatch($schema, $graphql, $di, '{ failure }');
verify('GraphQL error formatter suppresses arbitrary exception extras', !str_contains(json_encode($r), 'private-secret') && !str_contains(json_encode($r), 'token-value'));
$response = new PhalconGraphQL\Http\Response();
$response->setDI($di);
$response->setErrorContent($e, false);
verify('HTTP exception formatter suppresses arbitrary exception extras', !str_contains($response->getContent(), 'private-secret') && !str_contains($response->getContent(), 'token-value'));

echo $checks, ' security checks passed on PHP ', PHP_VERSION, ', Phalcon ', phpversion('phalcon'), ', graphql-php ', Composer\InstalledVersions::getPrettyVersion('webonyx/graphql-php'), ".\n";
