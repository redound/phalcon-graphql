<?php

namespace PhalconGraphQL\Plugins\Authorization;

use Phalcon\Di;
use Phalcon\DiInterface;
use PhalconApi\Acl\MountingEnabledAdapterInterface as MountingEnabledAclAdapterInterface;
use PhalconApi\Constants\ErrorCodes;
use PhalconGraphQL\Exception;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Plugins\Plugin;

class AclAuthorizationPlugin extends Plugin
{
    public function afterBuildSchema(Schema $schema, DiInterface $di)
    {
        $acl = $di->get(Services::ACL);

        if($acl instanceof MountingEnabledAclAdapterInterface){
            $acl->mount($schema);
        }
    }

    public function beforeResolve(Schema $schema, ObjectType $objectType, Field $field)
    {
        $di = Di::getDefault();
        $userService = $di->get(Services::USER_SERVICE);
        $acl = $di->get(Services::ACL);

        $allowed = $acl->isAllowed($userService->getRole(), $objectType->getName(), $field->getName());

        if (!$allowed) {
            throw new Exception(ErrorCodes::ACCESS_DENIED, 'No access to field "' . $field->getName() . '" on object "' . $objectType->getName() . '"');
        }
    }
}