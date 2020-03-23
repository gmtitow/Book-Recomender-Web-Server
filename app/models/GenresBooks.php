<?php

namespace App\Models;

class GenresBooks extends AbstractModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $genre_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $book_id;

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
     * Returns the value of field genre_id
     *
     * @return integer
     */
    public function getGenreId()
    {
        return $this->genre_id;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        
		$this->setSource(self::getTableName());
        $this->belongsTo('book_id', 'App\Models\Books', 'book_id', ['alias' => 'Books']);
        $this->belongsTo('genre_id', 'App\Models\Genres', 'genre_id', ['alias' => 'Genres']);
    }

	public static function getTableName() {
		return 'genres_books';
	}
}
