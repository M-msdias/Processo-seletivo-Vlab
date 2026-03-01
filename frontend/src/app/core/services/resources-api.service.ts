import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import {
  Resource,
  PaginatedResponse,
  CreateResourceDto,
  UpdateResourceDto
} from '../models/resource.model';
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class ResourcesApiService {
  private readonly baseUrl = `${environment.apiUrl}/resources`;

  constructor(private http: HttpClient) {}

  getAll(perPage = 15): Observable<PaginatedResponse<Resource>> {
    const params = new HttpParams().set('per_page', perPage);
    return this.http.get<PaginatedResponse<Resource>>(this.baseUrl, { params });
  }

  getById(id: number): Observable<Resource> {
    return this.http.get<Resource>(`${this.baseUrl}/${id}`);
  }

  create(dto: CreateResourceDto): Observable<Resource> {
    return this.http.post<Resource>(this.baseUrl, dto);
  }

  update(id: number, dto: UpdateResourceDto): Observable<Resource> {
    return this.http.put<Resource>(`${this.baseUrl}/${id}`, dto);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }
}
