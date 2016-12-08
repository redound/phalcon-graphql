<?php

namespace PhalconGraphQL\Plugins\Authorization;

use Phalcon\DiInterface;
use PhalconApi\Acl\MountingEnabledAdapterInterface as MountingEnabledAclAdapterInterface;
use PhalconApi\Constants\ErrorCodes;
use PhalconApi\Exception;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Definition\Fields\Field;
use PhalconGraphQL\Definition\ObjectType;
use PhalconGraphQL\Definition\Schema;
use PhalconGraphQL\Plugins\Plugin;

class AclAuthorizationPlugin extends Plugin
{
    /** @var \Phalcon\Acl\AdapterInterface  */
    protected $_acl;

    /** @var  \PhalconApi\User\Service */
    protected $_userService;

    public function __construct($userService = null, $acl = null)
    {
        $this->_userService = $userService;
        $this->_acl = $acl;
    }

    public function afterBuildSchema(Schema $schema, DiInterface $di)
    {
        /** @var \Phalcon\Acl\AdapterInterface $acl */
        if(!$this->_acl){
            $this->_acl = $di->get(Services::ACL);
        }

        if(!$this->_userService){
            $this->_userService = $di->get(Services::USER_SERVICE);
        }

        if($this->_acl instanceof MountingEnabledAclAdapterInterface){
            $this->_acl->mount($schema);
        }
    }

    public function beforeResolve(Schema $schema, ObjectType $objectType, Field $field)
    {
        $allowed = $this->_acl->isAllowed($this->_userService->getRole(), $objectType->getName(), $field->getName());

        if (!$allowed) {
            throw new Exception(ErrorCodes::ACCESS_DENIED, 'No access to field "' . $field->getName() . '" on object "' . $objectType->getName() . '"');
        }
    }
}