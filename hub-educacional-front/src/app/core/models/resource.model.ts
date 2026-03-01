export type ResourceType = 'video' | 'pdf' | 'link';

export interface Resource {
  id: number;
  title: string;
  description: string;
  type: ResourceType;
  url: string;
  tags: string[];
  created_at?: string;
  updated_at?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

export interface CreateResourceDto {
  title: string;
  description: string;
  type: ResourceType;
  url: string;
  tags: string[];
}

export type UpdateResourceDto = Partial<CreateResourceDto>;

export interface SmartAssistRequest {
  title: string;
  url?: string;
}

export interface SmartAssistResponse {
  description: string;
  tags: string[];
}
