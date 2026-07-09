<?php
namespace App\core;
use League\Plates\Engine;

abstract class Controller
{
    protected $plates;

    public function __construct()
    {
        try {
            $pathviews = dirname(__FILE__,2).DIRECTORY_SEPARATOR.'views';
            
            // Verificar se o diretório de views existe
            if (!is_dir($pathviews)) {
                throw new \Exception("Diretório de views não encontrado: " . $pathviews);
            }
            
            // Verificar se a classe Engine existe (biblioteca instalada)
            if (!class_exists('League\Plates\Engine')) {
                throw new \Exception("Biblioteca League\Plates\Engine não encontrada. Execute: composer install");
            }
            
            $this->plates = new Engine($pathviews);
            
            // Verificar se a instância foi criada corretamente
            if ($this->plates === null) {
                throw new \Exception("Falha ao criar instância do Engine do Plates");
            }
            
            // Adicionar a função getCurrentPage ao Plates
            $this->plates->registerFunction('getCurrentPage', function() {
                $uri = $_SERVER['REQUEST_URI'];
                // Remove query string
                $uri = strtok($uri, '?');
                // Remove /admin/
                $uri = str_replace('/admin/', '', $uri);
                // Pega a primeira parte da URI
                $page = explode('/', $uri)[0];
                return $page === '' ? 'dashboard' : $page;
            });
            
        } catch (\Exception $e) {
            error_log("Erro ao inicializar Controller: " . $e->getMessage());
            // Fallback: criar uma instância básica ou usar renderização simples
            $this->plates = null;
        }
    }

    public function view(string $view, array $data = [])
    {
        try {
            // Verificar se o Plates foi inicializado corretamente
            if ($this->plates === null) {
                throw new \Exception("Engine do Plates não foi inicializado");
            }
            
            echo $this->plates->render($view, $data);
            
        } catch (\Exception $e) {
            error_log("Erro ao renderizar view '$view': " . $e->getMessage());
            
            // Fallback: renderização simples sem template engine
            $this->renderFallback($view, $data);
        }
    }
    
    /**
     * Método de fallback para renderização quando o Plates falha
     */
    private function renderFallback(string $view, array $data = [])
    {
        try {
            // Extrair variáveis para o escopo da view
            extract($data);
            
            // Construir o caminho do arquivo de view
            $pathviews = dirname(__FILE__,2).DIRECTORY_SEPARATOR.'views';
            $viewFile = $pathviews . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';
            
            // Verificar se o arquivo existe
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                // Última tentativa: buscar arquivo .phtml
                $viewFilePhtml = $pathviews . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.phtml';
                if (file_exists($viewFilePhtml)) {
                    include $viewFilePhtml;
                } else {
                    throw new \Exception("Arquivo de view não encontrado: $viewFile");
                }
            }
            
        } catch (\Exception $e) {
            error_log("Erro no fallback de renderização: " . $e->getMessage());
            
            // Última tentativa: exibir erro amigável
            echo "<div style='padding: 20px; border: 1px solid #ccc; margin: 20px; background: #f9f9f9;'>";
            echo "<h3>Erro de Sistema</h3>";
            echo "<p>Ocorreu um erro ao carregar a página. Tente novamente mais tarde.</p>";
            echo "<p><small>Erro técnico: " . htmlspecialchars($e->getMessage()) . "</small></p>";
            echo "</div>";
        }
    }
    
    public function layout(string $layout, array $data = [])
    {
        return $this->view($layout, $data);
    }
}

