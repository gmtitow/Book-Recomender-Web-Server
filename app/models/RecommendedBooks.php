<?php

namespace App\Models;

use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;

class RecommendedBooks extends AbstractModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $list_id;

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
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $created_at;

    /**
     *
     * @var double
     * @Column(type="double", nullable=false)
     */
    protected $accordance;

    /**
     * @return int
     */
    public function getListId(): int
    {
        return $this->list_id;
    }

    /**
     * @param int $list_id
     */
    public function setListId(int $list_id): void
    {
        $this->list_id = $list_id;
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
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     */
    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return float
     */
    public function getAccordance(): float
    {
        return $this->accordance;
    }

    /**
     * @param float $accordance
     */
    public function setAccordance(float $accordance): void
    {
        $this->accordance = $accordance;
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
        return 'recommended_books';
    }
}
