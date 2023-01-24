<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"L'image doit être mentionnée")]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max:255, maxMessage:"Le caption ne peut dépasser 255 caractères")]
    private ?string $caption = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message:"Le pattern doit être mentionné")]
    private ?Pattern $id_pattern = null;

  

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function getIdPattern(): ?Pattern
    {
        return $this->id_pattern;
    }

    public function setIdPattern(?Pattern $id_pattern): self
    {
        $this->id_pattern = $id_pattern;

        return $this;
    }

}
