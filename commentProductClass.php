<?php

class CommentProduct extends ObjectModel
{
	
    public $id_comment;
    public $id_product;
    public $id_shop;
    public $id_user;
    public $name;
    public $comment;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'product_icomment',
        'primary' => 'id_comment',
        // 'multilang' => true,
        // 'multilang_shop' => true,
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT),
            'id_shop' => array('type' => self::TYPE_INT),
            'id_user' => array('type' => self::TYPE_INT),
            'name' => array('type' => self::TYPE_STRING),
            'comment' => array('type' => self::TYPE_STRING),
        ),
    );
}