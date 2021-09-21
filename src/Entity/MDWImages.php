<?php

namespace App\Entity;

use App\Repository\MDWImagesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MDWImagesRepository::class)
 */
class MDWImages
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:carte:MDWProduit'])]
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity=MDWProduits::class, inversedBy="images")
     * @ORM\JoinColumn(nullable=false)
     */
    private $produit;

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

    public function getProduit(): ?MDWProduits
    {
        return $this->produit;
    }

    public function setProduit(?MDWProduits $produit): self
    {
        $this->produit = $produit;

        return $this;
    }
}
