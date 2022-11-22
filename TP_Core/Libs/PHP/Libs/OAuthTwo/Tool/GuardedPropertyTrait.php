<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 04:31
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Tool;
if(ABSPATH){
    trait GuardedPropertyTrait{
        protected $_guarded = [];
        protected function _fillProperties(array $options = []): void
        {
            if (isset($options['guarded'])) unset($options['guarded']);
            foreach ($options as $option => $value) {
                if (property_exists($this, $option) && !$this->isGuarded($option))
                    $this->{$option} = $value;
            }
            return null;
        }
        public function getGuarded(): array
        {
            return $this->_guarded;
        }
        public function isGuarded($property): bool
        {
            return in_array($property, $this->getGuarded(), true);
        }
    }
}else die;