import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import {
  SmartAssistRequest,
  SmartAssistResponse
} from '../models/resource.model';
import { environment } from '../../../environments/environment';


@Injectable({ providedIn: 'root' })
export class SmartAssistApiService {
  private readonly baseUrl = `${environment.apiUrl}/resources/smart-assist`;

  constructor(private http: HttpClient) {}

  generate(request: SmartAssistRequest): Observable<SmartAssistResponse> {
    return this.http.post<SmartAssistResponse>(this.baseUrl, request);
  }
}
