<?php

namespace Art4\YouthwebEvent\Model;

use Doctrine_EntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Member entity
 */
class MemberModel
{
	/**
	 * Loads the metadata for the specified class into the provided container.
	 *
	 * @param ClassMetadata $metadata
	 *
	 * @return void
	 */
	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);

		//$metadata->setCustomRepositoryClass(MemberRepository::class);

		$metadata->setPrimaryTable([
			'name' => 'members',
		]);

		$metadata->mapField([
			'id' => true,
			'fieldName' => 'id',
			'columnName' => 'id',
			'type' => 'integer',
			'length' => 5,
		]);

		$metadata->mapField([
			'fieldName' => 'user_id',
			'columnName' => 'user_id',
			'type' => 'integer',
			'length' => 9,
		]);

		$metadata->mapField([
			'fieldName' => 'username',
			'columnName' => 'username',
			'type' => 'string',
			'length' => 255,
		]);

		$metadata->mapField([
			'fieldName' => 'name',
			'columnName' => 'name',
			'type' => 'string',
			'length' => 255,
		]);

		$metadata->mapField([
			'fieldName' => 'member_since',
			'columnName' => 'member_since',
			'type' => 'datetimetz',
		]);

		$metadata->mapField([
			'fieldName' => 'birthday',
			'columnName' => 'birthday',
			'type' => 'date',
		]);

		$metadata->mapField([
			'fieldName' => 'picture_url',
			'columnName' => 'picture_url',
			'type' => 'string',
			'length' => 255,
		]);

		$metadata->mapField([
			'fieldName' => 'description_motto',
			'columnName' => 'description_motto',
			'type' => 'text',
		]);

		$metadata->mapField([
			'fieldName' => 'created_at',
			'columnName' => 'created_at',
			'type' => 'integer',
			'length' => 14,
		]);
	}

	/**
	 * Member-ID
	 *
	 * @var integer
	 */
	private $id;

	/**
	 * User-ID
	 *
	 * @var integer
	 */
	private $user_id;

	/**
	 * Username
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Name
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * Member since
	 *
	 * @var \DateTimeInterface
	 */
	private $member_since;

	/**
	 * Birthday
	 *
	 * @var \DateTimeInterface
	 */
	private $birthday;

	/**
	 * picture_url
	 *
	 * @var string
	 */
	private $picture_url;

	/**
	 * Description motto
	 *
	 * @var string
	 */
	private $description_motto = '';

	/**
	 * Created_at
	 *
	 * @var integer
	 */
	private $created_at;

	/**
	 * Get the value of Client-ID
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the value of User-ID
	 *
	 * @return integer
	 */
	public function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Set the value of User-ID
	 *
	 * @param integer user_id
	 *
	 * @return self
	 */
	public function setUserId($user_id)
	{
		$this->user_id = $user_id;

		return $this;
	}

	/**
	 * Get the value of username
	 *
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set the value of Name
	 *
	 * @param string name
	 *
	 * @return self
	 */
	public function setUsername($username)
	{
		$this->username = strval($username);

		return $this;
	}

	/**
	 * Get the value of Name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the value of Name
	 *
	 * @param string name
	 *
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = strval($name);

		return $this;
	}

	/**
	 * Get the value of member_since
	 *
	 * @return \DateTimeInteface|null
	 */
	public function getMemberSince()
	{
		return $this->member_since;
	}

	/**
	 * Set the value of Name
	 *
	 * @param \DateTimeInteface|null $member_since
	 *
	 * @return self
	 */
	public function setMemberSince($member_since)
	{
		$this->member_since = $member_since;

		return $this;
	}

	/**
	 * Get the value of birthday
	 *
	 * @return \DateTimeInteface|null
	 */
	public function getBirthday()
	{
		return $this->birthday;
	}

	/**
	 * Set the value of birthday
	 *
	 * @param \DateTimeInteface|null $birthday
	 *
	 * @return self
	 */
	public function setBirthday($birthday)
	{
		$this->birthday = $birthday;

		return $this;
	}

	/**
	 * Get the value of Url
	 *
	 * @return string
	 */
	public function getPictureUrl()
	{
		return $this->picture_url;
	}

	/**
	 * Set the value of Url
	 *
	 * @param string url
	 *
	 * @return self
	 */
	public function setPictureUrl($picture_url)
	{
		$this->picture_url = $picture_url;

		return $this;
	}

	/**
	 * Get the value of Description
	 *
	 * @return string
	 */
	public function getDescriptionMotto()
	{
		return $this->description_motto;
	}

	/**
	 * Set the value of Description
	 *
	 * @param string $description_motto
	 *
	 * @return self
	 */
	public function setDescriptionMotto($description_motto)
	{
		$this->description_motto = strval($description_motto);

		return $this;
	}

	/**
	 * Get the value of Created_at
	 *
	 * @return integer
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * Set the value of Created_at
	 *
	 * @param integer created_at
	 *
	 * @return self
	 */
	public function setCreatedAt($created_at)
	{
		$this->created_at = $created_at;

		return $this;
	}
}
