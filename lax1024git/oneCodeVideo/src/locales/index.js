import { createI18n } from 'vue-i18n'
import es from './es'
import cn from './cn'

const messages = { es,cn };
const i18n = createI18n({
  locale: 'es', 
  fallbackLocale: 'cn',
  messages,
  legacy: false
});
export default i18n;
