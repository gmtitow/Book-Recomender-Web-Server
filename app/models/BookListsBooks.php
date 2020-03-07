<?php

namespace App\Models;

class BookListsBooks extends AbstractModel
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
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $book_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $created_at;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $rating;

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
     * Method to set the value of field book_id
     *
     * @param integer $book_id
     * @return $this
     */
    public function setBookId($book_id)
    {
        $this->book_id = $book_id;

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
     * Method to set the value of field rating
     *
     * @param integer $rating
     * @return $this
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

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
     * Returns the value of field book_id
     *
     * @return integer
     */
    public function getBookId()
    {
        return $this->book_id;
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
     * Returns the value of field rating
     *
     * @return integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("book_lists_books");
        $this->belongsTo('book_id', 'App\Models\Books', 'book_id', ['alias' => 'Books']);
        $this->belongsTo('list_id', 'App\Models\BookLists', 'list_id', ['alias' => 'BookLists']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'book_lists_books';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BookListsBooks[]|BookListsBooks|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BookListsBooks|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
