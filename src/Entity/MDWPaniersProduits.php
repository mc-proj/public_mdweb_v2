<?php

namespace App\Entity;

use App\Repository\MDWPaniersProduitsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MDWPaniersProduitsRepository::class)
 */
class MDWPaniersProduits
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantite;

    /**
     * @ORM\ManyToOne(targetEntity=MDWProduits::class, inversedBy="paniers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $produit;

    /**
     * @ORM\ManyToOne(targetEntity=MDWPaniers::class, inversedBy="produits")
     * @ORM\JoinColumn(nullable=false)
     */
    private $panier;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

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

    public function getPanier(): ?MDWPaniers
    {
        return $this->panier;
    }

    public function setPanier(?MDWPaniers $panier): self
    {
        $this->panier = $panier;

        return $this;
    }
}
