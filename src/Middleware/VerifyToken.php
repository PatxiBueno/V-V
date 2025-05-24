<?php

namespace TwitchAnalytics\Middleware;

use Closure;
use Illuminate\Http\Request;

require_once __DIR__ . '/../../bbdd/conexion.php';
class VerifyToken
{
    public function handle(Request $request, Closure $next)
    {
        $headers = $request->headers->all();
        if (!$this->validateToken($headers)) {
            return response()->json(['error' => 'Unauthorized. Token is invalid or expired.'], 401);
        }

        return $next($request);
    }

    public function validateToken($headers)
    {
        if (!isset($headers['authorization'])) {
            return false;
        }

        list($bearer, $tokenUsuario) = explode(" ", $headers['authorization'][0], 2);

        if (strcasecmp($bearer, "Bearer") != 0) {
            return false;
        }
        $con = conexion();
        $resultado = $this->getUserTokenFromDataBase($tokenUsuario, $con);

        if (!$resultado || $resultado->num_rows === 0) {
            return false;
        }

        $fila = $resultado->fetch_assoc();
        $timestampToken = $fila['fecha_token'];
        $tiempoDelToken = time() - strtotime($timestampToken);

        if ($tiempoDelToken > 259200) {
            return false;
        }
        return true;
    }

    public function getUserTokenFromDataBase(string $userToken, ?\mysqli $con): bool|\mysqli_result
    {
        $queryToken = "SELECT fecha_token FROM token WHERE token LIKE  '$userToken'";
        $resultado = $con->query($queryToken);
        return $resultado;
    }
}
