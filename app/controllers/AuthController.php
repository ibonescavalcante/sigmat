<?php

namespace App\controllers;
use App\middleware\SessionSecurity;
use App\core\Controller;
use App\models\Usuario;
use App\models\UsuarioSessaoDashboard;
use App\helpers\BrandingServidores;



class AuthController extends Controller
{


    public function index()
    {  
      
        if (SessionSecurity::estaLogadoDashboard()) {               
         $this->view('dashboard/main');
            exit;
        }
      
        $data = [];
        if (isset($_GET['sessao']) && $_GET['sessao'] === 'substituida') {
            $data['erro'] = 'Sessão encerrada. Esta conta foi acessada em outro dispositivo ou navegador.';
        }


        $this->view('login/page', $data);
    }    

    public function logar()
    {

       

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $_POST é sempre um array
            $dados = $_POST;

            // Acesso correto para array
            $username = isset($dados['username']) ? trim($dados['username']) : '';
            $password = isset($dados['password']) ? trim($dados['password']) : '';

        
            if (empty($username)) {
                $this->view('login/page', ['erro' => 'Usuário ou senha inválido!']);
                return;
            } elseif (empty($password)) {
                $this->view('login/page', ['erro' => 'Usuário ou senha inválido!']);
                return;
            } else {
                // Autenticação
                $confirma_senha = Usuario::autentica_uauario($_POST['username'], $_POST['password']);

                    //   var_dump($confirma_senha); die;

                if ($confirma_senha) {
                    try {
                        if (session_status() === PHP_SESSION_ACTIVE) {
                            session_regenerate_id(true);
                        }

     
                        $token = bin2hex(random_bytes(32));
                        UsuarioSessaoDashboard::registrarOuAtualizar((int) $confirma_senha['id'], $token);
                
                        $perfilSessao = strtolower(trim((string) ($confirma_senha['perfil'] ?? '')));
         
                        $_SESSION['user'] = [
                            'id' => $confirma_senha['id'],
                            'nome' => $confirma_senha['nome'],
                            'username' => $confirma_senha['usuario'] ?? '',
                            'perfil' => $perfilSessao,
                            'dashboard_sessao_token' => $token,
                        ];
                        SessionSecurity::regenerateDashboardCsrfToken();


                        header('Location: /');
                        exit;
                    } catch (\Throwable $e) {
                        error_log('Login dashboard sessão: ' . $e->getMessage());
                        $this->view('login/page', [
                            'erro' => 'Não foi possível concluir o login. Tente novamente ou contate o suporte.',
                        ]);
                        return;
                    }
                } else {
                    $this->view('login/page', ['erro' => 'Usuário ou senha inválido!']);
                }
            }
        }
    }

    public function logout()
    {
        if (SessionSecurity::estaLogadoDashboard()) {
            $uid = (int) ($_SESSION['user']['id'] ?? 0);
            if ($uid > 0) {
                try {
                    UsuarioSessaoDashboard::removerPorUsuario($uid);
                } catch (\Throwable $e) {
                    error_log('logout dashboard: ' . $e->getMessage());
                }
            }
        }

        SessionSecurity::destruirSessao();
        header('Location: /login');
        exit;
    }

}
