<?php

require 'smarty/Smarty.class.php';

/**
 * Classe permettant de gérer le rendu HTML d'une page.
 *
 * @author marechal
 */
class HtmlRenderer implements Renderer {
    /**
     * Instance de Smarty.
     * @var type Smarty.
     */
    private $smarty;
    
    /**
     * Layout utilisé.
     * @var string.
     */
    public $pageLayout;

    /**
     * Constructeur.
     */
    public function __construct() {
        // Instatiation du moteur Smarty
        $this->smarty = new Smarty();
        // Activer le cache Smarty
        $this->smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
        // Spécifier le répertoire de cache
        $this->smarty->setCacheDir(SMARTY_CACHE_FOLDER . DIRECTORY_SEPARATOR . "result");
        // Spécifier l'endroi où mettre les pages compilés 
        $this->smarty->setCompileDir(SMARTY_CACHE_FOLDER . DIRECTORY_SEPARATOR ."compile");
    }
    
    /**
     * Indiquer la page à afficher et vérifie qu'elle peut être appelée.
     * @param string $pagePath Chemin souhaité.
     * @return bool <code>true</code> si initialisation réussie, <code>false</code> sinon.
     */
    public function setPage($pagePath){
        // Résoudre le chemin du répertoire demandée
        $realPagePath = realpath(PAGE_FOLDER . DIRECTORY_SEPARATOR . $pagePath);
        
        // Si le répertoire existe
        if (file_exists($realPagePath)            
        // S'il est dans le répertoire pages
        && startsWith($realPagePath, PAGE_FOLDER)
        // Et que le fichier data.php existe 
        && file_exists($realPagePath . DIRECTORY_SEPARATOR . 'data.php')){
            // Assigner le layout par défaut
            $this->pageLayout = realpath(PAGE_FOLDER . DIRECTORY_SEPARATOR . "layout.tpl");            

            // Charger les spécifications de la page
            require($realPagePath . DIRECTORY_SEPARATOR . 'data.php');

            // La page a été initialisée
            return true;                
        }
        
        // Echec de l'initialisation de la page
        return false;
    }
    
    /**
     * Renvoi le rendu du template en fonction des données envoyés.
     * @param string $pagePath Chemin souhaité.
     * @param array $pageDatas Données du template.
     * @param bool $reloadCache Doit-on recharger le cache ?
     * @return string Le rendu généré par le moteur.
     */
    public function render($pagePath, $pageDatas, $reloadCache = false){        
        // Résoudre le chemin du répertoire demandée
        $realPagePath = realpath(PAGE_FOLDER . DIRECTORY_SEPARATOR . $pagePath) . DIRECTORY_SEPARATOR;
        
        // Si on doit recharger le cache ou que la page n'a pas été mise en cache
        if ($reloadCache ||!$this->smarty->isCached($realPagePath . 'content.tpl')){
            
            // Nettoyer le cache pour ce fichier
            $this->smarty->clearCache($realPagePath . 'content.tpl');
        }
        
        // Assigner les données au template si elles existent
        if (!empty($pageDatas)){
            foreach($pageDatas as $dataName => $dataValue){
                $this->smarty->assign($dataName, $dataValue);
            }            
        }

        // Assigner le js relatif à la page s'il existe
        $this->smarty->assign("js", file_exists($realPagePath . "script.js") 
            ? file_get_contents($realPagePath . "script.js") : "");

        // Assigner le corps de page
        $this->smarty->assign("bodyContent", $realPagePath . "content.tpl");

        // Assigner la racine du site
        $this->smarty->assign("rootURL", BASE_URL);
        
        // Afficher le template
        return $this->smarty->fetch($this->pageLayout);
    }
}
