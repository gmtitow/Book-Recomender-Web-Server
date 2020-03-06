<?php

namespace App\Models;

use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;

class Reviews extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $review_id;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    protected $review_text;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $rating;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $book_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $review_date;

    /**
     * Method to set the value of field review_id
     *
     * @param integer $review_id
     * @return $this
     */
    public function setReviewId($review_id)
    {
        $this->review_id = $review_id;

        return $this;
    }

    /**
     * Method to set the value of field review_text
     *
     * @param string $review_text
     * @return $this
     */
    public function setReviewText($review_text)
    {
        $this->review_text = $review_text;

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
     * Method to set the value of field review_date
     *
     * @param integer $review_date
     * @return $this
     */
    public function setReviewDate($review_date)
    {
        $this->review_date = $review_date;

        return $this;
    }

    /**
     * Returns the value of field review_id
     *
     * @return integer
     */
    public function getReviewId()
    {
        return $this->review_id;
    }

    /**
     * Returns the value of field review_text
     *
     * @return string
     */
    public function getReviewText()
    {
        return $this->review_text;
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
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
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
     * Returns the value of field review_date
     *
     * @return string
     */
    public function getReviewDate()
    {
        return $this->review_date;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("reviews");
        $this->belongsTo('book_id', 'App\Models\Books', 'book_id', ['alias' => 'Books']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'reviews';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Reviews[]|Reviews|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Reviews|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findForBook($bookId, $page, $page_size) {
        $query = new CustomQuery([
           'from'=>'reviews r',
           'where'=> 'book_id = :book_id',
           'order' => 'review_date desc',
           'bind'=>['book_id'=>$bookId]
        ]);

        $result = SupportClass::executeWithPagination($query->getSql(),$query->getBind(),$page,$page_size);

        return $result;
    }

    public static function findForUser($userId, $page, $page_size) {
        $query = new CustomQuery([
            'columns'=>'reviews.*, books.name, books.description, authors.full_name as author_full_name',
            'from'=>'reviews inner join books using(book_id) inner join authors using(author_id)',
            'where'=> 'user_id = :user_id',
            'order' => 'review_date desc',
            'bind'=>['user_id'=>$userId]
        ]);

        $result = SupportClass::executeWithPagination($query->getSql(),$query->getBind(),$page,$page_size);

        return $result;
    }

    public static function checkReviewExists(int $user_id, int $book_id) : bool {
        $reviews = Reviews::findFirst(['user_id = :user_id: and book_id = :book_id:',
            'bind'=>['user_id'=>$user_id,'book_id'=>$book_id]]);
        
        return $reviews!==false;
    }
}
