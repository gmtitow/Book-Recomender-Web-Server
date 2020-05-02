<?php

namespace App\Models;

class BookLists extends AbstractModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $list_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    protected $list_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $user_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $created_at;

    /**
     *
     * @var boolean
     * @Column(type="boolean", nullable=false)
     */
    protected $is_main;

    /**
     * Method to set the value of field list_id
     *
     * @param integer $list_id
     * @return $this
     */
    public function setListId($list_id)
    {
        $this->list_id = $list_id;

        return $this;
    }

    /**
     * Method to set the value of field list_name
     *
     * @param string $list_name
     * @return $this
     */
    public function setListName($list_name)
    {
        $this->list_name = $list_name;

        return $this;
    }

    /**
     * Method to set the value of field user_id
     *
     * @param integer $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Method to set the value of field created_at
     *
     * @param string $created_at
     * @return $this
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @param boolean $is_main
     * @return $this
     */
    public function setIsMain($is_main)
    {
        $this->is_main = $is_main;

        return $this;
    }

    /**
     * Returns the value of field list_id
     *
     * @return integer
     */
    public function getListId()
    {
        return $this->list_id;
    }

    /**
     * Returns the value of field list_name
     *
     * @return string
     */
    public function getListName()
    {
        return $this->list_name;
    }

    /**
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     *
     * @return boolean
     */
    public function getIsMain()
    {
        return $this->is_main;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        
		$this->setSource(self::getTableName());
        $this->hasMany('list_id', 'App\Models\BookListsBooks', 'list_id', ['alias' => 'BookListsBooks']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

	public static function getTableName() {
		return 'book_lists';
	}

    public static function getIdField()
    {
        return 'list_id';
    }
}
