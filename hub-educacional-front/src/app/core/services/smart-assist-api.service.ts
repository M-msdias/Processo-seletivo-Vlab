import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import {
  SmartAssistRequest,
  SmartAssistResponse
} from '../models/resource.model';

@Injectable({ providedIn: 'root' })
export class SmartAssistApiService {
  private readonly url =
    'http://localhost:8000/api/recursos/assistencia-inteligente';

  constructor(private http: HttpClient) {}

  generate(request: SmartAssistRequest): Observable<SmartAssistResponse> {
    return this.http.post<SmartAssistResponse>(this.url, request);
  }
}
