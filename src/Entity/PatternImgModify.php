<?php 

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PatternImgModify{

    #[Assert\Image(mimeTypes:["image/png","image/jpeg","image/jpg"], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, png")]
    #[Assert\File(maxSize:"1024k", maxSizeMessage:"La taille du fichier est trop grande")]
    private ?string $newCover = null;

    public function getNewCover(): ?string
    {
        return $this->newCover;
    }

    public function setNewCover(?string $newCover): self
    {
        $this->newCover = $newCover;

        return $this;
    }
}
