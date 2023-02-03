<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PatternRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: PatternRepository::class)]
class Pattern
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Le titre du motif doit être mentionné")]
    #[Assert\Length(min: 2, max:255, minMessage:"Le titre doit faire au minimum 2 caractères", maxMessage: "Le titre ne peut dépasser 255 caractères")]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Le thème du motif doit être mentionné")]
    #[Assert\Length(min: 2, max:255, minMessage:"Le thème du motif doit faire au minimum 2 caractères", maxMessage: "Le thème du motif ne peut dépasser 255 caractères")]
    private ?string $theme = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"La couleur dominante du motif doit être mentionnée")]
    #[Assert\Length(min: 2, max:255, minMessage:"La couleur dominante du motif doit faire au minimum 2 caractères", maxMessage: "La couleur dominante du motif ne peut dépasser 255 caractères")]
    private ?string $dominantColor = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message:"La date de création doit être mentionnée")]
    #[Assert\LessThan("today", message:'Doit être ultérieure à aujourd\'hui', groups:['front'])]
    #[Assert\Type('datetime')]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\Image(mimeTypes:["image/png","image/jpeg","image/jpg"], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, png ou gif")]
    #[Assert\Image(maxSize:"2000000", maxSizeMessage:"Votre fichier dépasse le poid maximal autorisé")]
    private ?string $cover = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Le type de licence accordée doit être mentionnée")]
    #[Assert\Choice(choices:['PRIVÉE','COMMERCIALE','PROTÉGÉE'])]
    private ?string $license = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:'LE slug doit être mentionné')]
    #[Assert\Length(min:2, max:255, minMessage:"Le slug doit posséder au minimum deux caractères", maxMessage:"Le slug ne peut atteindre plue de 255 caractères")]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'patterns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $id_user = null;

    #[ORM\OneToMany(mappedBy: 'id_pattern', targetEntity: Image::class, orphanRemoval: true)]
    private Collection $images;

    #[ORM\Column(nullable: true)]
    private ?int $nb_like = null;

    #[ORM\OneToMany(mappedBy: 'id_pattern', targetEntity: Comment::class)]
    private Collection $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

  

    /**
     * Initialisation automatique du slug 
     *
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlug():void{
        if (empty($this->slug)){
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title.'-'.rand());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getDominantColor(): ?string
    {
        return $this->dominantColor;
    }

    public function setDominantColor(string $dominantColor): self
    {
        $this->dominantColor = $dominantColor;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(string $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }

    public function setIdUser(?User $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setIdPattern($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getIdPattern() === $this) {
                $image->setIdPattern(null);
            }
        }

        return $this;
    }

    public function getNbLike(): ?int
    {
        return $this->nb_like;
    }

    public function setNbLike(?int $nb_like): self
    {
        $this->nb_like = $nb_like;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setIdPattern($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getIdPattern() === $this) {
                $comment->setIdPattern(null);
            }
        }

        return $this;
    }
}
