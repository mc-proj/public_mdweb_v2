<?php

namespace App\Entity;

use App\Repository\MDWFacturesProduitsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MDWFacturesProduitsRepository::class)
 */
class MDWFacturesProduits
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
     * @ORM\ManyToOne(targetEntity=MDWProduits::class, inversedBy="factures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $produit;

    /**
     * @ORM\ManyToOne(targetEntity=MDWFactures::class, inversedBy="produit")
     * @ORM\JoinColumn(nullable=false)
     */
    private $facture;

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

    public function getFacture(): ?MDWFactures
    {
        return $this->facture;
    }

    public function setFacture(?MDWFactures $facture): self
    {
        $this->facture = $facture;

        return $this;
    }
}
