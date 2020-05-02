<?php

namespace App\Models;

class Authors extends AbstractModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $author_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    protected $first_name;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    protected $last_name;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    protected $full_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $birthday;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date_adding;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $reading_id;

    /**
     * Method to set the value of field author_id
     *
     * @param integer $author_id
     * @return $this
     */
    public function setAuthorId($author_id)
    {
        $this->author_id = $author_id;

        return $this;
    }

    /**
     * Method to set the value of field first_name
     *
     * @param string $first_name
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * Method to set the value of field last_name
     *
     * @param string $last_name
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * Method to set the value of field full_name
     *
     * @param string $full_name
     * @return $this
     */
    public function setFullName($full_name)
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * Method to set the value of field birthday
     *
     * @param string $birthday
     * @return $this
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Method to set the value of field date_adding
     *
     * @param string $date_adding
     * @return $this
     */
    public function setDateAdding($date_adding)
    {
        $this->date_adding = $date_adding;

        return $this;
    }

    /**
     * Method to set the value of field reading_id
     *
     * @param integer $reading_id
     * @return $this
     */
    public function setReadingId($reading_id)
    {
        $this->reading_id = $reading_id;

        return $this;
    }

    /**
     * Returns the value of field author_id
     *
     * @return integer
     */
    public function getAuthorId()
    {
        return $this->author_id;
    }

    /**
     * Returns the value of field first_name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Returns the value of field last_name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Returns the value of field full_name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Returns the value of field birthday
     *
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Returns the value of field date_adding
     *
     * @return string
     */
    public function getDateAdding()
    {
        return $this->date_adding;
    }

    /**
     * Returns the value of field reading_id
     *
     * @return integer
     */
    public function getReadingId()
    {
        return $this->reading_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        
		$this->setSource(self::getTableName());
        $this->hasMany('author_id', 'App\Models\Books', 'author_id', ['alias' => 'Books']);
    }

	public static function getTableName() {
		return 'authors';
	}

    public static function getIdField()
    {
        return 'author_id';
    }
}
