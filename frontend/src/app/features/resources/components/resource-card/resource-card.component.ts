import { Component, input, output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Resource } from '../../../../core/models/resource.model';


@Component({
  selector: 'app-resource-card',
  imports: [CommonModule],
  templateUrl: './resource-card.component.html',
  styleUrl: './resource-card.component.scss',
})
export class ResourceCardComponent {
  readonly resource = input.required<Resource>();
  readonly edit = output<Resource>();
  readonly delete = output<number>();

  typeIcon(): string {
    return { video: '🎬', pdf: '📄', link: '🔗' }[this.resource().type];
  }
}
