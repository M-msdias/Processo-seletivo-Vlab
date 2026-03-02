import { Injectable, computed } from '@angular/core';
import { ResourcesState } from '../state/resources.state';
import { ResourcesApiService } from '../../../core/services/resources-api.service';
import { SmartAssistApiService } from '../../../core/services/smart-assist-api.service';
import {
  Resource,
  CreateResourceDto,
  UpdateResourceDto
} from '../../../core/models/resource.model';



@Injectable()
export class ResourcesFacade {

  readonly resources = computed(() => this.state.state().resources);
  readonly loading = computed(() => this.state.state().loading);
  readonly error = computed(() => this.state.state().error);
  readonly aiLoading = computed(() => this.state.state().aiLoading);
  readonly formVisible = computed(() => this.state.state().formVisible);
  readonly formMode = computed(() => this.state.state().formMode);
  readonly selectedResource = computed(() => this.state.state().selectedResource);
  readonly searchQuery = computed(() => this.state.state().searchQuery);
  readonly aiResult = computed(() => this.state.state().aiResult);

  readonly mutating = computed(() => this.state.state().mutating);

  readonly totalCount = computed(() => this.state.state().resources.length);

  readonly totalRecords = computed(() => this.state.state().totalRecords);
  readonly currentPage = computed(() => this.state.state().currentPage);
  readonly perPage = computed(() => this.state.state().perPage);

  readonly filteredResources = computed(() => {
    const query = this.state.state().searchQuery.toLowerCase().trim();
    const resources = this.state.state().resources;
    if (!query) return resources;
    return resources.filter(r =>
      r.title.toLowerCase().includes(query) ||
      r.tags.some(t => t.toLowerCase().includes(query))
    );
  });

  constructor(
    private state: ResourcesState,
    private resourcesApi: ResourcesApiService,
    private smartAssistApi: SmartAssistApiService
  ) {}

  loadResources(page?: number): void {
    const targetPage = page || this.state.state().currentPage;
    const perPage = this.state.state().perPage;

    this.state.patch({ loading: true, error: null });
    
    this.resourcesApi.getAll(targetPage, perPage).subscribe({
      next: (response) => {
        this.state.patch({ 
          resources: response.data, 
          totalRecords: response.meta.total,       
          currentPage: response.meta.current_page,  
          perPage: response.meta.per_page,          
          loading: false,
        });
      },
      error: err => {
        this.state.patch({ loading: false, error: err.message });
      },
    });
  }

  changePage(event: any): void {
    const newPage = event.page + 1; 
    const rows = event.rows;
    
    this.state.patch({ perPage: rows, currentPage: newPage });
    this.loadResources(newPage);
  }

  createResource(dto: CreateResourceDto): void {
    this.state.patch({ mutating: true });
    this.resourcesApi.create(dto).subscribe({
      next: resource => {
        const resources = [resource, ...this.state.state().resources];
        this.state.patch({ resources, mutating: false, formVisible: false });
      },
      error: err => this.state.patch({ mutating: false, error: err.message }),
    });
  }

  updateResource(id: number, dto: UpdateResourceDto): void {
    this.state.patch({ mutating: true });
    this.resourcesApi.update(id, dto).subscribe({
      next: updated => {
        const resources = this.state.state().resources
          .map(r => r.id === id ? updated : r);
        this.state.patch({
          resources,
          formVisible: false,
          mutating: false,
          selectedResource: null
        });
      },
      error: err => this.state.patch({ mutating: false, error: err.message }),
    });
  }

  deleteResource(id: number): void {
    this.state.patch({ mutating: true });
    this.resourcesApi.delete(id).subscribe({
      next: () => {
        const resources = this.state.state().resources.filter(r => r.id !== id);
        this.state.patch({ resources, mutating: false });
      },
      error: err => this.state.patch({ error: err.message, mutating: false }),
    });
  }

  generateAiDescription(title: string, type?: string): void {
    this.state.patch({ aiLoading: true });
    this.smartAssistApi.generate({ title, type }).subscribe({
      next: result => {
        this.state.patch({
          aiLoading: false,
          aiResult: result  
        });
      },
      error: () => this.state.patch({ aiLoading: false }),
    });
  }

  openCreateForm(): void {
    this.state.patch({ formVisible: true, formMode: 'create', selectedResource: null });
  }

  openEditForm(resource: Resource): void {
    this.state.patch({ formVisible: true, formMode: 'edit', selectedResource: resource });
  }

  closeForm(): void {
    this.state.patch({ formVisible: false, selectedResource: null });
  }

  setSearchQuery(query: string): void {
    this.state.patch({ searchQuery: query });
  }
}
