<?php

namespace App\Models;

class Genres extends AbstractModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $genre_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    protected $genre_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $reading_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $vector;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    protected $genre_name_english;

    /**
     * Method to set the value of field genre_id
     *
     * @param integer $genre_id
     * @return $this
     */
    public function setGenreId($genre_id)
    {
        $this->genre_id = $genre_id;

        return $this;
    }

    /**
     * Method to set the value of field genre_name
     *
     * @param string $genre_name
     * @return $this
     */
    public function setGenreName($genre_name)
    {
        $this->genre_name = $genre_name;

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
     * Method to set the value of field vector
     *
     * @param string $vector
     * @return $this
     */
    public function setVector($vector)
    {
        $this->vector = $vector;

        return $this;
    }

    /**
     * Method to set the value of field genre_name_english
     *
     * @param string $genre_name_english
     * @return $this
     */
    public function setGenreNameEnglish($genre_name_english)
    {
        $this->genre_name_english = $genre_name_english;

        return $this;
    }

    /**
     * Returns the value of field genre_id
     *
     * @return integer
     */
    public function getGenreId()
    {
        return $this->genre_id;
    }

    /**
     * Returns the value of field genre_name
     *
     * @return string
     */
    public function getGenreName()
    {
        return $this->genre_name;
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
     * Returns the value of field vector
     *
     * @return string
     */
    public function getVector()
    {
        return $this->vector;
    }

    /**
     * Returns the value of field genre_name_english
     *
     * @return string
     */
    public function getGenreNameEnglish()
    {
        return $this->genre_name_english;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("genres");
        $this->hasMany('genre_id', 'App\Models\GenresBooks', 'genre_id', ['alias' => 'GenresBooks']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'genres';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Genres[]|Genres|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Genres|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
