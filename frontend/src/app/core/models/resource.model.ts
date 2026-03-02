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
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
    links: {
      url: string | null;
      label: string;
      active: boolean;
    }[];
  };
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
  type?: string;
}

export interface SmartAssistResponse {
  description: string;
  tags: string[];
}
