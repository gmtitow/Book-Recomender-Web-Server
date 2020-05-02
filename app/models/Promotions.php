<?php

namespace App\Models;

class Promotions extends AbstractModel
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
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(type="timestamp", nullable=false)
     */
    protected $time_start;

    /**
     *
     * @var string
     * @Column(type="timestamp", nullable=false)
     */
    protected $time_end;

    /**
     *
     * @var string
     * @Column(type="timestamp", nullable=false)
     */
    protected $created_at;

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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getTimeStart(): string
    {
        return $this->time_start;
    }

    /**
     * @param string $time_start
     */
    public function setTimeStart(string $time_start): void
    {
        $this->time_start = $time_start;
    }

    /**
     * @return string
     */
    public function getTimeEnd(): string
    {
        return $this->time_end;
    }

    /**
     * @param string $time_end
     */
    public function setTimeEnd(string $time_end): void
    {
        $this->time_end = $time_end;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        
		$this->setSource(self::getTableName());
    }

	public static function getTableName() {
		return 'promotions';
	}

    public static function getIdField()
    {
        return 'promotion_id';
    }
}
