<?php

namespace App\Models;

class BooksFiles extends AbstractModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $book_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $file_id;

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
     * Method to set the value of field file_id
     *
     * @param integer $file_id
     * @return $this
     */
    public function setFileId($file_id)
    {
        $this->file_id = $file_id;

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
     * Returns the value of field file_id
     *
     * @return integer
     */
    public function getFileId()
    {
        return $this->file_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        
		$this->setSource(self::getTableName());
        $this->belongsTo('book_id', 'App\Models\Books', 'book_id', ['alias' => 'Books']);
        $this->belongsTo('file_id', 'App\Models\Files', 'file_id', ['alias' => 'Files']);
    }

	public static function getTableName() {
		return 'books_files';
	}
}
