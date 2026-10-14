<?php

namespace PhalconGraphQL\Plugins\Authorization;

use Phalcon\Di\DiInterface;
use PhalconApi\Acl\MountingEnabledAdapterInterface as MountingEnabledAclAdapterInterface;
use PhalconApi\Constants\ErrorCodes;
use PhalconGraphQL\Exception;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Plugins\Plugin;
use PhalconGraphQL\Plugins\ExecutionPluginInterface;
use PhalconGraphQL\Plugins\SchemaPluginInterface;

class AclAuthorizationPlugin extends Plugin implements ExecutionPluginInterface, SchemaPluginInterface
{
    public function afterBuildSchema(Schema $schema, DiInterface $di)
    {
        self::mountSchema($schema, $di);
    }

    public function beforeResolve(Schema $schema, ObjectType $objectType, Field $field)
    {
        throw new \LogicException('ACL authorization requires the dispatch DI context');
    }

    public function beforeResolveWithContext(Schema $schema, ObjectType $objectType, Field $field, DiInterface $di)
    {
        if (!$di->has(Services::USER_SERVICE) || !$di->has(Services::ACL)) {
            throw new Exception(ErrorCodes::ACCESS_DENIED, 'Authorization context is unavailable');
        }

        $userService = $di->get(Services::USER_SERVICE);
        $acl = $di->get(Services::ACL);

        if (!$userService instanceof \PhalconApi\User\Service || !$acl instanceof \Phalcon\Acl\Adapter\AdapterInterface) {
            throw new Exception(ErrorCodes::ACCESS_DENIED, 'Authorization context is unavailable');
        }

        $role = $userService->getRole();
        $allowed = $role !== null && $acl->isAllowed($role, $objectType->getName(), $field->getName());

        if (!$allowed) {
            throw new Exception(ErrorCodes::ACCESS_DENIED, 'No access to field "' . $field->getName() . '" on object "' . $objectType->getName() . '"');
        }
    }

    public static function mountSchema(Schema $schema, DiInterface $di)
    {
        $acl = $di->get(Services::ACL);

        if($acl instanceof MountingEnabledAclAdapterInterface){
            $acl->mount($schema);
        }
    }
}
