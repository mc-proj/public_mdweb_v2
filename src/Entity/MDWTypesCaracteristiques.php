<?php

namespace App\Entity;

use App\Repository\MDWTypesCaracteristiquesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MDWTypesCaracteristiquesRepository::class)
 */
class MDWTypesCaracteristiques
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
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=MDWCaracteristiques::class, mappedBy="type_caracteristique", orphanRemoval=true)
     */
    private $caracteristiques;

    public function __construct()
    {
        $this->caracteristiques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection|MDWCaracteristiques[]
     */
    public function getCaracteristiques(): Collection
    {
        return $this->caracteristiques;
    }

    public function addCaracteristique(MDWCaracteristiques $caracteristique): self
    {
        if (!$this->caracteristiques->contains($caracteristique)) {
            $this->caracteristiques[] = $caracteristique;
            $caracteristique->setTypeCaracteristique($this);
        }

        return $this;
    }

    public function removeCaracteristique(MDWCaracteristiques $caracteristique): self
    {
        if ($this->caracteristiques->removeElement($caracteristique)) {
            // set the owning side to null (unless already changed)
            if ($caracteristique->getTypeCaracteristique() === $this) {
                $caracteristique->setTypeCaracteristique(null);
            }
        }

        return $this;
    }
}
