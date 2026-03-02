
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ResourcesFacade } from '../../facades/resources.facade';
import { SearchBarComponent } from '../../components/search-bar/search-bar.component';
import { ResourceCardComponent } from '../../components/resource-card/resource-card.component';
import { ResourceFormComponent } from '../../components/resource-form/resource-form.component';
import { CreateResourceDto } from '../../../../core/models/resource.model';
import { FooterComponent } from '../../../../layout/footer/footer.component';
import { ResourcesState } from '../../state/resources.state';
import { PaginatorModule } from 'primeng/paginator';

@Component({
  selector: 'app-resources-page',
  imports: [
    CommonModule,
    SearchBarComponent,
    ResourceCardComponent,
    ResourceFormComponent,
    FooterComponent,
    PaginatorModule
  ],
  providers: [ResourcesState, ResourcesFacade],
  templateUrl: './resources-page.component.html',
  styleUrl: './resources-page.component.scss',
})
export class ResourcesPageComponent implements OnInit {
  constructor(public facade: ResourcesFacade) {}

  ngOnInit(): void {
    this.facade.loadResources();
  }

  onFormSubmit(dto: CreateResourceDto): void {
    const selected = this.facade.selectedResource();
    if (this.facade.formMode() === 'create') {
      this.facade.createResource(dto);
    } else if (selected) {
      this.facade.updateResource(selected.id, dto);
    }
  }

}
