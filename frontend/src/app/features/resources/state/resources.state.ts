import { Injectable, signal, computed } from '@angular/core';
import { Resource } from '../../../core/models/resource.model';

export interface ResourcesStateModel {
  resources: Resource[];
  loading: boolean;
  error: string | null;
  searchQuery: string;
  selectedResource: Resource | null;
  formVisible: boolean;
  formMode: 'create' | 'edit';
  aiLoading: boolean;
  aiResult: { description: string; tags: string[] } | null;
  mutating: boolean;

  totalRecords: number;
  currentPage: number;
  perPage: number;
}

const initialState: ResourcesStateModel = {
  resources: [],
  loading: false,
  error: null,
  searchQuery: '',
  selectedResource: null,
  formVisible: false,
  formMode: 'create',
  aiLoading: false,
  aiResult: null,
  mutating: false,

  totalRecords: 0,
  currentPage: 1,
  perPage: 15,
};

@Injectable()
export class ResourcesState {
  private readonly _state = signal<ResourcesStateModel>(initialState);

  readonly state = this._state.asReadonly();

  patch(partial: Partial<ResourcesStateModel>): void {
    this._state.update(current => ({ ...current, ...partial }));
  }

  reset(): void {
    this._state.set(initialState);
  }
}
