<?php

namespace App\Entity;

use App\Repository\MDWCategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MDWCategoriesRepository::class)
 */
class MDWCategories
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
    #[Groups(['read:facture:MDWFacture'])]
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\ManyToMany(targetEntity=MDWProduits::class, mappedBy="categories")
     */
    private $produits;

    /**
     * @ORM\ManyToMany(targetEntity=MDWCategories::class, inversedBy="categories_parentes")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $sous_categories;

    /**
     * @ORM\ManyToMany(targetEntity=MDWCategories::class, mappedBy="sous_categories")
     */
    private $categories_parentes;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->sous_categories = new ArrayCollection();
        $this->categories_parentes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
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
            $produit->addCategory($this);
        }

        return $this;
    }

    public function removeProduit(MDWProduits $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            $produit->removeCategory($this);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getSousCategories(): Collection
    {
        return $this->sous_categories;
    }

    public function addSousCategory(self $sousCategory): self
    {
        if (!$this->sous_categories->contains($sousCategory)) {
            $this->sous_categories[] = $sousCategory;
        }

        return $this;
    }

    public function removeSousCategory(self $sousCategory): self
    {
        $this->sous_categories->removeElement($sousCategory);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getCategoriesParentes(): Collection
    {
        return $this->categories_parentes;
    }

    public function addCategoriesParente(self $categoriesParente): self
    {
        if (!$this->categories_parentes->contains($categoriesParente)) {
            $this->categories_parentes[] = $categoriesParente;
            $categoriesParente->addSousCategory($this);
        }

        return $this;
    }

    public function removeCategoriesParente(self $categoriesParente): self
    {
        if ($this->categories_parentes->removeElement($categoriesParente)) {
            $categoriesParente->removeSousCategory($this);
        }

        return $this;
    }
}
