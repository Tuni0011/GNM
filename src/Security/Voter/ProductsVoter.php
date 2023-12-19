<?php

namespace App\Security\Voter;



use App\Entity\Products;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProductsVoter extends Voter
        {

        
            
                const EDIT ='PRODUCT_EDIT';
                const DELETE ='PRODUCT_DELETE';

                private $security;

                    public function __construct(Security $security)
                        {
                            $this->security = $security;
                        }

            
                    protected function supports($attribute, $products) :bool
                        {
                            // if the attribute isn't one we support, return false
                            if (!in_array($attribute, [self::EDIT, self::DELETE])) {
                                return false;
                            }

                            // only vote on Post objects inside this voter
                            if (!$products instanceof Products) {
                                return false;
                            }

                            return true;
                            
                        }
            
                    protected function voteOnAttribute($attribute, $products, TokenInterface $token) :bool
                        {
                            $user = $token->getUser();
                            // if the user is anonymous, do not grant access
                            if (!$user instanceof UserInterface) {
                                return false;
                            }


                            // ... (check conditions and return true to grant permission) ...

                            if ($this->security->isGranted('ROLE_ADMIN')) { return true; }


                            switch ($attribute) {
                                case self::EDIT:
                                    // logic to determine if the user can EDIT
                                   return $this->canEdit();
                                    break;
                                case self::DELETE:
                                    // logic to determine if the user can DELETE
                                    // return true or false
                                    break;
                            }

                            return false;



                        }

                        private function canEdit()
                            {
                                return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
                            }
                        private function canDelete()
                        {
                                return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
                            }


                       
        }
