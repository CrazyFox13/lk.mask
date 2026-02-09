export const findBanner = (banners: any[], key: string) => {
    return banners.find((b: any) => b?.place?.key === key);
}