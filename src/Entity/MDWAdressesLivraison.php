<?php

namespace App\Entity;

use App\Repository\MDWAdressesLivraisonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MDWAdressesLivraisonRepository::class)
 */
class MDWAdressesLivraison
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $adresse;

    /**
     * @ORM\Column(type="string", length=45)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $ville;

    /**
     * @ORM\Column(type="string", length=45)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $code_postal;

    /**
     * @ORM\Column(type="string", length=45)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $pays;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $telephone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\Column(type="datetime")
     */
    private $derniere_modification;

    /**
     * @ORM\OneToMany(targetEntity=MDWFactures::class, mappedBy="adresseLivraison")
     */
    private $Factures;

    public function __construct()
    {
        $this->Factures = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(string $code_postal): self
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getDerniereModification(): ?\DateTimeInterface
    {
        return $this->derniere_modification;
    }

    public function setDerniereModification(\DateTimeInterface $derniere_modification): self
    {
        $this->derniere_modification = $derniere_modification;

        return $this;
    }

    /**
     * @return Collection|MDWFactures[]
     */
    public function getFactures(): Collection
    {
        return $this->Factures;
    }

    public function addFacture(MDWFactures $facture): self
    {
        if (!$this->Factures->contains($facture)) {
            $this->Factures[] = $facture;
            $facture->setAdresseLivraison($this);
        }

        return $this;
    }

    public function removeFacture(MDWFactures $facture): self
    {
        if ($this->Factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getAdresseLivraison() === $this) {
                $facture->setAdresseLivraison(null);
            }
        }

        return $this;
    }
}
