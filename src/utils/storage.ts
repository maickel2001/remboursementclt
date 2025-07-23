import { Remboursement, AppSettings } from '../types';

export const getRemboursements = (): Remboursement[] => {
  return JSON.parse(localStorage.getItem('remboursements') || '[]');
};

export const saveRemboursement = (remboursement: Remboursement): void => {
  const remboursements = getRemboursements();
  remboursements.push(remboursement);
  localStorage.setItem('remboursements', JSON.stringify(remboursements));
};

export const updateRemboursementStatus = (id: string, status: 'en_attente' | 'valide' | 'refuse'): void => {
  const remboursements = getRemboursements();
  const updated = remboursements.map(r => 
    r.id === id ? { ...r, status, updatedAt: new Date().toISOString() } : r
  );
  localStorage.setItem('remboursements', JSON.stringify(updated));
};

export const getAppSettings = (): AppSettings => {
  const defaultSettings: AppSettings = {
    siteName: 'RemboursePRO',
    contactEmail: 'contact@remboursepro.com',
    maintenanceMode: false
  };
  
  return {
    ...defaultSettings,
    ...JSON.parse(localStorage.getItem('appSettings') || '{}')
  };
};

export const saveAppSettings = (settings: AppSettings): void => {
  localStorage.setItem('appSettings', JSON.stringify(settings));
};