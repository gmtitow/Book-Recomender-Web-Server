<?php

namespace App\Models;

class PromotionsBooks extends AbstractModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $promotion_id;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $book_id;

    /**
     *
     * @var integer
     * @Column(type="bigint", nullable=true)
     */
    protected $price;

    /**
     * @return int
     */
    public function getPromotionId(): int
    {
        return $this->promotion_id;
    }

    /**
     * @param int $promotion_id
     */
    public function setPromotionId(int $promotion_id): void
    {
        $this->promotion_id = $promotion_id;
    }

    /**
     * @return int
     */
    public function getBookId(): int
    {
        return $this->book_id;
    }

    /**
     * @param int $book_id
     */
    public function setBookId(int $book_id): void
    {
        $this->book_id = $book_id;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        
		$this->setSource(self::getTableName());
    }

	public static function getTableName() {
		return 'promotions_books';
	}

    public static function getIdField()
    {
        return ['promotion_id','book_id'];
    }
}
