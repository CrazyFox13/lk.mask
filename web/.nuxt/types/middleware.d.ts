import type { NavigationGuard } from 'vue-router'
export type MiddlewareKey = "auth-redirect" | "auth" | "block-customer-offers" | "block-customer-orders" | "block-customer-portfolio" | "block-customer-recommendations"
declare module "../../node_modules/nuxt/dist/pages/runtime/composables" {
  interface PageMeta {
    middleware?: MiddlewareKey | NavigationGuard | Array<MiddlewareKey | NavigationGuard>
  }
}
declare module 'nitropack' {
  interface NitroRouteConfig {
    appMiddleware?: MiddlewareKey | MiddlewareKey[] | Record<MiddlewareKey, boolean>
  }
}