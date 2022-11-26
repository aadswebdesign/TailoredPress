<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-4-2022
 * Time: 16:46
 */
namespace TP_Core\Libs\SimplePie\Factory;
if(ABSPATH){
    trait _object_for_cache{
        protected function _prepare_simplepie_object_for_cache($data): array
        {
            $items = $data->this->get_items();
            $items_by_id = [];
            if (!empty($items)){
                foreach ($items as $item) $items_by_id[$item->this->get_id()] = $item;
                if (count($items_by_id) !== count($items)){
                    $items_by_id = array();
                    foreach ($items as $item) $items_by_id[$item->this->get_id(true)] = $item;
                }
                if (isset($data->data['child'][SP_NS_ATOM_10]['feed'][0]))
                    $channel =& $data->data['child'][SP_NS_ATOM_10]['feed'][0];
                elseif (isset($data->data['child'][SP_NS_ATOM_03]['feed'][0]))
                    $channel =& $data->data['child'][SP_NS_ATOM_03]['feed'][0];
                elseif (isset($data->data['child'][SP_NS_RDF]['RDF'][0]))
                    $channel =& $data->data['child'][SP_NS_RDF]['RDF'][0];
                elseif (isset($data->data['child'][SP_NS_RSS_20]['rss'][0]['child'][SP_NS_RSS_20]['channel'][0]))
                    $channel =& $data->data['child'][SP_NS_RSS_20]['rss'][0]['child'][SP_NS_RSS_20]['channel'][0];
                else $channel = null;
                if ($channel !== null){
                    if (isset($channel['child'][SP_NS_ATOM_10]['entry']))
                        unset($channel['child'][SP_NS_ATOM_10]['entry']);
                    if (isset($channel['child'][SP_NS_ATOM_03]['entry']))
                        unset($channel['child'][SP_NS_ATOM_03]['entry']);
                    if (isset($channel['child'][SP_NS_RSS_10]['item']))
                        unset($channel['child'][SP_NS_RSS_10]['item']);
                    if (isset($channel['child'][SP_NS_RSS_090]['item']))
                        unset($channel['child'][SP_NS_RSS_090]['item']);
                    if (isset($channel['child'][SP_NS_RSS_20]['item']))
                        unset($channel['child'][SP_NS_RSS_20]['item']);
                }
                if (isset($data->data['items'])) unset($data->data['items']);
                if (isset($data->data['ordered_items'])) unset($data->data['ordered_items']);
            }
            return array(serialize($data->data), $items_by_id);
        }
    }
}else die;