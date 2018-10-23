<?php
namespace Numerique1\Components\Restresources\Model;

/**
 * Class ResourceInterface
 * @package Numerique1\Components\Restresources\Model
 */
interface ResourceInterface
{
    const CAN_LIST ='list';
    const CAN_RETRIEVE ='retrieve';
    const CAN_CREATE ='create';
    const CAN_UPDATE ='update';
    const CAN_DELETE ='delete';
}