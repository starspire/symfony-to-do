<?php

namespace AppBundle\Entity;

use AppBundle\Constants\TaskPriority;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Task.
 *
 * @ORM\Table(name="task")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TaskRepository")
 * @Vich\Uploadable
 * @Hateoas\Relation("self",
 *     href= @Hateoas\Route(
 *     "api_get_task",
 *     parameters = { "id" = "expr(object.getId())" }
 *     )
 * )
 * @Hateoas\Relation("parent",
 *     href= @Hateoas\Route(
 *     "api_get_tasks"
 *      )
 * )
 */
class Task
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="3",minMessage = "Subject must be at least {{ limit }} characters long")
     */
    private $subject;

    /**
     * @var bool
     *
     * @ORM\Column(name="done", type="boolean")
     */
    private $done = false;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="tasks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Exclude()
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer")
     * @Assert\NotBlank()
     */
    private $priority = 0;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category", inversedBy="tasks")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var ArrayCollection|Tag[]
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", inversedBy="tasks", cascade={"persist"})
     * @ORM\JoinTable(name="tasks_tags")
     */
    private $tags;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="task_image", fileNameProperty="imageName", size="imageSize")
     *
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $imageName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $imageSize;

    /**
     * @return int|null
     */
    public function getImageSize()
    {
        return $this->imageSize;
    }

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set subject.
     *
     * @param string $subject
     *
     * @return Task
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set done.
     *
     * @param bool $done
     *
     * @return Task
     */
    public function setDone($done)
    {
        $this->done = $done;

        return $this;
    }

    /**
     * Get done.
     *
     * @return bool
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Task
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int
     * @Serializer\VirtualProperty()
     */
    public function getUserId()
    {
        return $this->getUser()->getId();
    }

    public static function getAvailablePriorities()
    {
        return [
            TaskPriority::NONE => 0,
            TaskPriority::URGENT => 1,
            TaskPriority::DEADLINE => 2,
        ];
    }

    /**
     * Set priority.
     *
     * @param int $priority
     *
     * @return Task
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function setPriorityByName($priority)
    {
        $this->priority = self::getAvailablePriorities()[$priority];

        return $this;
    }

    /**
     * Get priority.
     *
     * @return string
     */
    public function getPriority()
    {
        return array_search($this->priority, self::getAvailablePriorities());
    }

    /**
     * Set category.
     *
     * @param Category $category
     *
     * @return Task
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag
     *
     * @return Task
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag.
     *
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function setTags(ArrayCollection $tags)
    {
        $this->tags = $tags;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * @return File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    /**
     * @return string|null
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;
    }
}
