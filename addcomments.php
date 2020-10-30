<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
*
*  https://devdocs.prestashop.com/1.7/modules/creation/
*  {hook h=''} {widget name='mymodule'} {widget name='mymodule' hook='leftcolum'}
*  https://build.prestashop.com/prestashop-ui-kit/    Ex Forms
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_.'addcomments/commentProductClass.php';

class Addcomments extends Module implements WidgetInterface
{
    private $templateFile;

	public function __construct()
	{
		$this->name = 'addcomments';
		$this->version = '1.0';
		$this->author = 'Med Baalla';
		$this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Add Comments');
        $this->description = $this->trans('Displays Comments on your shop.');

        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:addcomments/views/templates/front/addcomments.tpl';
    }
	
	
    public function install()
    {
        // install Data and Hooks
        if (parent::install() && 
            $this->registerHook('displayFooterProduct') && 
            $this->installDB() 
        ) {
            return true;
        }

        $this->_errors[] = $this->trans('There was an error during the installation. Please contact us through Addons website.', array(), 'Modules.Blockreassurance.Admin');

        return false;
	}
	
	
    public function installDB()
    {
        $sqlQueries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'product_icomment` (
            `id_comment` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_product` int(10) NOT NULL,
            `id_shop` int(10) NULL,
            `id_user` int(10) NULL,
            `name` varchar(255) NOT NULL,
            `comment` varchar(255) NOT NULL,
            PRIMARY KEY (`id_comment`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';
		
        // $sqlQueries[] = 'INSERT INTO ' . _DB_PREFIX_ . 'product_comment (xx) VALUES (xx)';
		
        foreach ($sqlQueries as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }

		return true;
	}
	
    public function uninstall()
    {
        // SQL
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'product_icomment`';
        if (Db::getInstance()->execute($sql) == false) {
            return false;
        }
		
        if (parent::uninstall()) {
            return true;
        }
		
        $this->_errors[] = $this->trans('There was an error during the uninstallation. Please contact us through Addons website.', array(), 'Modules.Blockreassurance.Admin');

        return false;
	}
	
	
    public function renderWidget($hookName, array $configuration)
    {
		$this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        return $this->fetch($this->templateFile);
    }

    public function getWidgetVariables($hookName, array $params)
    {
		$message = 'Nothing';
		$comments = array();
		//{$smarty->server.HTTP_HOST}{$smarty->server.request_uri}
		if (Tools::isSubmit('comment')) {
			// print_r(Tools::getAllValues());
			$commentProduct = new CommentProduct();
			$commentProduct->id_product = Tools::getValue('id_product');
			// $commentProduct->id_shop = 1;
			// $commentProduct->id_user = 1;
			$commentProduct->name = Tools::getValue('name');
			$commentProduct->comment = Tools::getValue('comment');
			///  Save
			if ($commentProduct->save()) {
				$message = 'well done';
			} else {
				$message = 'Error';
			}
		}
		
		
		if (Tools::getValue('id_product')) {
			$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'product_icomment` WHERE `id_product` = ' . (int)Tools::getValue('id_product') ;
			$comments = Db::getInstance()->executeS($sql);
		}
	
		return array(
			"message"=> $message,
			"comments"=> $comments,
		);
    }
	
	
	public function getContent()
	{
		$output = null;		

		if (Tools::isSubmit('submit'.$this->name)) {

			//{$smarty->server.HTTP_HOST}{$smarty->server.request_uri}
			if (!empty(Tools::getValue('comment'))) {
				// print_r(Tools::getAllValues());
				$commentProduct = new CommentProduct();
				$commentProduct->id_product = Tools::getValue('id_product');
				// $commentProduct->id_shop = 1;
				// $commentProduct->id_user = 1;
				$commentProduct->name = Tools::getValue('name');
				$commentProduct->comment = Tools::getValue('comment');
				///  Save
				if ($commentProduct->save()) {
					$output .= $this->displayConfirmation('well done');
				} else {
					$output .= $this->displayError('Error');
				}
			} else {
				Configuration::updateValue('comment', 'fill this inputs');
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}

		return $output.$this->listComments();
	}
	
	
	public function listComments()
	{
		
		$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'product_icomment`';
		$comments = Db::getInstance()->executeS($sql);
		print_r($this->context->link->getModuleLink('mymodule', 'display'));
		
		$this->context->smarty->assign(
			[
				'comments' => $comments,
				'my_module_name' => Configuration::get('MYMODULE_NAME'),
				'my_module_link' => $this->context->link->getModuleLink('mymodule', 'display'),
				'my_module_message' => $this->l('This is a simple text message') // Do not forget to enclose your strings in the l() translation method
			]
		);

		return $this->display(__FILE__, 'views/templates/admin/list.tpl');
	}
	
	
	public function displayForm2()
	{
		// Get default language
		$defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

		// Init Fields form array
		$fieldsForm[0]['form'] = [
			'legend' => [
				'title' => $this->l('Add Comment'),
			],
			'input' => [
				[
					'type' => 'text',
					'label' => $this->l('Name'),
					'name' => 'name',
					'size' => 20,
					'required' => true
				],
				[
					'type' => 'textarea',
					'label' => $this->l('Comment'),
					'name' => 'comment',
					'size' => 20,
					'required' => true
				]
			],
			'submit' => [
				'title' => $this->l('Save'),
				'class' => 'btn btn-default pull-right'
			]
		];

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $defaultLang;
		$helper->allow_employee_form_lang = $defaultLang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = [
			'save' => [
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			],
			'back' => [
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			]
		];

		// Load current value
		$helper->fields_value['name'] = Tools::getValue('name', Configuration::get('name'));
		$helper->fields_value['comment'] = Tools::getValue('comment', Configuration::get('comment'));

		return $helper->generateForm($fieldsForm);
	}
}
