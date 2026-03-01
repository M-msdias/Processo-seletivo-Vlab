import { Component, input, output, OnInit, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Resource, CreateResourceDto, ResourceType } from '../../../../core/models/resource.model';


@Component({
  selector: 'app-resource-form',
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './resource-form.component.html',
  styleUrl: './resource-form.component.scss',
})
export class ResourceFormComponent implements OnInit { 
  readonly mode = input<'create' | 'edit'>('create');
  readonly resource = input<Resource | null>(null);
  readonly aiLoading = input<boolean>(false);

  readonly submit = output<CreateResourceDto>();
  readonly cancel = output<void>();
  readonly requestAi = output<{ title: string; url: string }>();

  form!: FormGroup;
  tags: string[] = [];

  readonly types = [
    { value: 'video' as ResourceType, label: 'Vídeo', icon: '🎬' },
    { value: 'pdf' as ResourceType, label: 'PDF', icon: '📄' },
    { value: 'link' as ResourceType, label: 'Link', icon: '🔗' },
  ];

  constructor(private fb: FormBuilder) {
    // Atualiza o form quando o recurso selecionado mudar (ex: IA preencheu descrição)
    effect(() => {
      const r = this.resource();
      if (r && this.form) {
        this.form.patchValue({
          title: r.title,
          type: r.type,
          url: r.url,
          description: r.description,
        });
        this.tags = [...r.tags];
      }
    });
  }

  ngOnInit(): void {
    const r = this.resource();
    this.form = this.fb.group({
      title: [r?.title || '', Validators.required],
      type: [r?.type || 'link', Validators.required],
      url: [r?.url || '', [Validators.required, Validators.pattern('https?://.+')]],
      description: [r?.description || ''],
    });
    this.tags = [...(r?.tags || [])];
  }

  addTag(value: string): void {
    const tag = value.trim();
    if (tag && !this.tags.includes(tag)) this.tags.push(tag);
  }

  removeTag(tag: string): void {
    this.tags = this.tags.filter(t => t !== tag);
  }

  onGenerateAi(): void {
    this.requestAi.emit({
      title: this.form.value.title,
      url: this.form.value.url,
    });
  }

  onSubmit(): void {
    if (this.form.valid) {
      this.submit.emit({ ...this.form.value, tags: this.tags });
    }
  }
}


