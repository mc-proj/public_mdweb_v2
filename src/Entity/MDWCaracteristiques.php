<?php

namespace App\Entity;

use App\Repository\MDWCaracteristiquesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MDWCaracteristiquesRepository::class)
 */
class MDWCaracteristiques
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
    private $valeur;

    /**
     * @ORM\ManyToOne(targetEntity=MDWProduits::class, inversedBy="caracteristiques")
     * @ORM\JoinColumn(nullable=false)
     */
    private $produit;

    /**
     * @ORM\ManyToOne(targetEntity=MDWTypesCaracteristiques::class, inversedBy="caracteristiques")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type_caracteristique;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(string $valeur): self
    {
        $this->valeur = $valeur;

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

    public function getTypeCaracteristique(): ?MDWTypesCaracteristiques
    {
        return $this->type_caracteristique;
    }

    public function setTypeCaracteristique(?MDWTypesCaracteristiques $type_caracteristique): self
    {
        $this->type_caracteristique = $type_caracteristique;

        return $this;
    }
}
