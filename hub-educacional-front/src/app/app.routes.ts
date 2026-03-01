import { Routes } from '@angular/router';
import { ResourcesPageComponent } from './features/resources/pages/resources-page/resources-page.component';

export const routes: Routes = [
    { path: '', component: ResourcesPageComponent, pathMatch: 'full' }
];
