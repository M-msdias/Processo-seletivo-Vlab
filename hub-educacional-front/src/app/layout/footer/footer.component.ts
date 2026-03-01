import { Component } from '@angular/core';

@Component({
  selector: 'app-footer',
  imports: [],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.scss',
})
export class FooterComponent {
    currentYear = new Date().getFullYear();

  navLinks = [
    { label: 'Início', href: '#' },
    { label: 'Meus Recursos', href: '#' },
    { label: 'Explorar', href: '#' },
    { label: 'Tags', href: '#' },
    { label: 'Contato', href: '#' }
  ];

  tags = ['Acessibilidade', 'Design', 'SCSS', 'Estilização', 'Educação', 'AI'];
}
