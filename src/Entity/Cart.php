<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, CartProduct>
     */
    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartProduct::class, cascade: ["persist"])]
    private Collection $cartProducts;

    #[ORM\OneToOne(inversedBy: 'cart', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->cartProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, CartProduct>
     */
    public function getCartProducts(): Collection
    {
        return $this->cartProducts;
    }

    public function addCartProduct(CartProduct $cartProduct): static
    {
        if (!$this->cartProducts->contains($cartProduct)) {
            $this->cartProducts->add($cartProduct);
            $cartProduct->setCart($this);
        } else { // si le produit existe déjà dans le panier
            $cartProduct->setQuantity($cartProduct->getQuantity() + 1);
        }

        return $this;
    }

    public function haveCartProduct(Product $product): CartProduct | null{
        foreach ($this->cartProducts as $cartProduct){
            if($cartProduct->getProduct()?->getId() === $product->getId()){
                return $cartProduct;
            }
        }

        return null;
    }

    public function removeCartProduct(CartProduct $cartProduct): static
    {
        if ($this->cartProducts->removeElement($cartProduct)) {
            // set the owning side to null (unless already changed)
            if ($cartProduct->getCart() === $this) {
                $cartProduct->setCart(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
