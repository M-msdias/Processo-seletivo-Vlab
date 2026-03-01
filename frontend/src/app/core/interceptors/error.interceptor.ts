import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { catchError, throwError } from 'rxjs';

export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      let message = 'Erro inesperado.';

      switch (error.status) {
        case 404:
          message = 'Recurso não encontrado.';
          break;
        case 422:
          message = 'Dados inválidos. Verifique os campos.';
          break;
        case 503:
          message = 'Serviço de IA indisponível no momento.';
          break;
        case 0:
          message = 'Sem conexão com o servidor.';
          break;
      }

      return throwError(() => new Error(message));
    })
  );
};
