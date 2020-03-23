<?php

namespace App\Models;

class Books extends AbstractModel
{

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
     * @Column(type="string", length=200, nullable=false)
     */
    protected $name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $author_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date_adding;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=true)
     */
    protected $rating_parsed;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $publishing_year;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $reading_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $translator_id;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=true)
     */
    protected $translate;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $has_translator;

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
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

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
     * Method to set the value of field rating_parsed
     *
     * @param string $rating_parsed
     * @return $this
     */
    public function setRatingParsed($rating_parsed)
    {
        $this->rating_parsed = $rating_parsed;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field publishing_year
     *
     * @param integer $publishing_year
     * @return $this
     */
    public function setPublishingYear($publishing_year)
    {
        $this->publishing_year = $publishing_year;

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
     * Method to set the value of field translator_id
     *
     * @param integer $translator_id
     * @return $this
     */
    public function setTranslatorId($translator_id)
    {
        $this->translator_id = $translator_id;

        return $this;
    }

    /**
     * Method to set the value of field translate
     *
     * @param string $translate
     * @return $this
     */
    public function setTranslate($translate)
    {
        $this->translate = $translate;

        return $this;
    }

    /**
     * Method to set the value of field has_translator
     *
     * @param string $has_translator
     * @return $this
     */
    public function setHasTranslator($has_translator)
    {
        $this->has_translator = $has_translator;

        return $this;
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
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Returns the value of field date_adding
     *
     * @return string
     */
    public function getDateAdding()
    {
        return $this->date_adding;
    }

    /**
     * Returns the value of field rating_parsed
     *
     * @return string
     */
    public function getRatingParsed()
    {
        return $this->rating_parsed;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field publishing_year
     *
     * @return integer
     */
    public function getPublishingYear()
    {
        return $this->publishing_year;
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
     * Returns the value of field translator_id
     *
     * @return integer
     */
    public function getTranslatorId()
    {
        return $this->translator_id;
    }

    /**
     * Returns the value of field translate
     *
     * @return string
     */
    public function getTranslate()
    {
        return $this->translate;
    }

    /**
     * Returns the value of field has_translator
     *
     * @return string
     */
    public function getHasTranslator()
    {
        return $this->has_translator;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        
		$this->setSource(self::getTableName());
        $this->hasMany('book_id', 'App\Models\BookVectorsModel', 'book_id', ['alias' => 'BookVectorsModel']);
        $this->hasMany('book_id', 'App\Models\BookVectorsPhraseNormal2', 'book_id', ['alias' => 'BookVectorsPhraseNormal2']);
        $this->hasMany('book_id', 'App\Models\BookVectorsPhraseNormal3', 'book_id', ['alias' => 'BookVectorsPhraseNormal3']);
        $this->hasMany('book_id', 'App\Models\BookVectorsPhraseNormal4', 'book_id', ['alias' => 'BookVectorsPhraseNormal4']);
        $this->hasMany('book_id', 'App\Models\BookVectorsPhraseWithoutNormal2', 'book_id', ['alias' => 'BookVectorsPhraseWithoutNormal2']);
        $this->hasMany('book_id', 'App\Models\BookVectorsPhraseWithoutNormal3', 'book_id', ['alias' => 'BookVectorsPhraseWithoutNormal3']);
        $this->hasMany('book_id', 'App\Models\BookVectorsPhraseWithoutNormal4', 'book_id', ['alias' => 'BookVectorsPhraseWithoutNormal4']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTerm2Normal', 'book_id', ['alias' => 'BookVectorsTerm2Normal']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTerm3Normal', 'book_id', ['alias' => 'BookVectorsTerm3Normal']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTerm4Normal', 'book_id', ['alias' => 'BookVectorsTerm4Normal']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTerm5Normal', 'book_id', ['alias' => 'BookVectorsTerm5Normal']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTerm6Normal', 'book_id', ['alias' => 'BookVectorsTerm6Normal']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTerm7Normal', 'book_id', ['alias' => 'BookVectorsTerm7Normal']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTermWithoutNormal2', 'book_id', ['alias' => 'BookVectorsTermWithoutNormal2']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTermWithoutNormal3', 'book_id', ['alias' => 'BookVectorsTermWithoutNormal3']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTermWithoutNormal4', 'book_id', ['alias' => 'BookVectorsTermWithoutNormal4']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTermWithoutNormal5', 'book_id', ['alias' => 'BookVectorsTermWithoutNormal5']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTermWithoutNormal6', 'book_id', ['alias' => 'BookVectorsTermWithoutNormal6']);
        $this->hasMany('book_id', 'App\Models\BookVectorsTermWithoutNormal7', 'book_id', ['alias' => 'BookVectorsTermWithoutNormal7']);
        $this->hasMany('book_id', 'App\Models\BookVectorsWordNormal', 'book_id', ['alias' => 'BookVectorsWordNormal']);
        $this->hasMany('book_id', 'App\Models\BookVectorsWordWithoutNormal', 'book_id', ['alias' => 'BookVectorsWordWithoutNormal']);
        $this->hasMany('book_id', 'App\Models\BooksFiles', 'book_id', ['alias' => 'BooksFiles']);
        $this->hasMany('book_id', 'App\Models\BooksStats', 'book_id', ['alias' => 'BooksStats']);
        $this->hasMany('book_id', 'App\Models\CompilationsBooks', 'book_id', ['alias' => 'CompilationsBooks']);
        $this->hasMany('book_id', 'App\Models\GenresBooks', 'book_id', ['alias' => 'GenresBooks']);
        $this->hasMany('book_id', 'App\Models\Reviews', 'book_id', ['alias' => 'Reviews']);
        $this->belongsTo('author_id', 'App\Models\Authors', 'author_id', ['alias' => 'Authors']);
        $this->belongsTo('translator_id', 'App\Models\Translators', 'translator_id', ['alias' => 'Translators']);
    }

	public static function getTableName() {
		return 'books';
	}
}
