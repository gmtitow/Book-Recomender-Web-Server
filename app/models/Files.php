<?php

namespace App\Models;

use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;

class Files extends AbstractModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $file_id;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    protected $full_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $created_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $extension;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $path_to;

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
     * Method to set the value of field extension
     *
     * @param string $extension
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

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
     * Method to set the value of field path_to
     *
     * @param string $path_to
     * @return $this
     */
    public function setPathTo($path_to)
    {
        $this->path_to = $path_to;

        return $this;
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
     * Returns the value of field full_name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
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
     * Returns the value of field extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
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
     * Returns the value of field path_to
     *
     * @return string
     */
    public function getPathTo()
    {
        return $this->path_to;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        
		$this->setSource(self::getTableName());
        $this->hasMany('file_id', 'App\Models\BooksFiles', 'file_id', ['alias' => 'BooksFiles']);
    }

    public static function findForBook(int $bookId, string $ext = 'txt') {
        $query = new CustomQuery([
            'columns'=>'files.*',
            'from'=>'files inner join books_files using(file_id)',
            'where'=>'book_id = :book_id and extension = :ext',
            'bind'=>['book_id'=>$bookId, 'ext'=>$ext]
        ]);

        return SupportClass::execute($query->getSql(),$query->getBind());
    }

	public static function getTableName() {
		return 'files';
	}
}
