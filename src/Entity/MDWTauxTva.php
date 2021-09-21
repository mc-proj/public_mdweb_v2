<?php

namespace App\Entity;

use App\Repository\MDWTauxTvaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MDWTauxTvaRepository::class)
 */
class MDWTauxTva
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
    private $intitule;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:carte:MDWProduit'])]
    private $taux;

    /**
     * @ORM\OneToMany(targetEntity=MDWProduits::class, mappedBy="taux_tva")
     */
    private $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getTaux(): ?int
    {
        return $this->taux;
    }

    public function setTaux(int $taux): self
    {
        $this->taux = $taux;

        return $this;
    }

    /**
     * @return Collection|MDWProduits[]
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(MDWProduits $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
            $produit->setTauxTva($this);
        }

        return $this;
    }

    public function removeProduit(MDWProduits $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getTauxTva() === $this) {
                $produit->setTauxTva(null);
            }
        }

        return $this;
    }
}
