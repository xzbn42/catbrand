<?php

defined('_JEXEC') or die('Restricted access');

class plgJshoppingRouterCategory_brand extends JPlugin
{

	var $query=array();
	var $segments=array();
	private $brand_id;
	private $brand_alias;
	private $label_id;

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->db=JFactory::getDBO();
	}

	function onBeforeBuildRoute(&$query, &$segments)
	{
		$this->query   =$query;
		$this->segments=$segments;
	}


	function onBeforeParseRoute(&$vars, &$segments)
	{
		$menu    =JFactory::getApplication()->getMenu();
		$menuItem=$menu->getActive();
		$link    =$menuItem->query;
		$brandsalias      =JSFactory::getAliasManufacturer();
		$brand_id=array_search(getSeoSegment(end($segments)), $brandsalias);
		if($link["option"]=="com_jshopping" && ($link["controller"]=="category" || $link["view"]=="category") AND $brand_id AND (int)$link['category_id']>0){
			$this->brand_id=$brand_id;
			$this->brand_alias=getSeoSegment(end($segments));
			$segments[0]='category';
			$segments[1]   ='view';
			$segments[2]   =$link['category_id'];
			$segments[3]   ='category_brand';
			if((int)$link['label_id']>0){
				$this->label_id=$link['label_id'];
			}
		}
	}


	function onAfterParseRoute(&$vars, &$segments)
	{
		if($segments[3]=='category_brand' AND (int)$this->brand_id>0){
			$vars['manufacturer_id']=$this->brand_id;
			JRequest::setVar('manufacturer_id', $this->brand_id);
			if((int)$this->label_id>0){
				$vars['label_id']=$this->label_id;
			}
			unset($segments[3]);
		}
	}

	public function onBeforeDisplayProductListView(&$view, &$productlist){
		if((int)$this->brand_id>0){
			$patterns=[];
			$patterns[0]="/(\?manufacturer_id=".$this->brand_id."&amp;)/";
			$patterns[1]="/(\?manufacturer_id=".$this->brand_id.")/";
			$replacements=[];
			$replacements[0]='/'.$this->brand_alias.'?';
			$replacements[1]='/'.$this->brand_alias;
			$view->pagination=preg_replace($patterns, $replacements, $view->pagination);
		}
	}
}