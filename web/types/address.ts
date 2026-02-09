export interface IAddress {
  address: string
  lat: number
  lng: number
  city: string | undefined
  geo_city_id: string | undefined
  fias_id: string | undefined
  region: string | undefined
  geo_region_id: number | undefined
  region_fias_id: string | undefined
}
