import ListLicensePackages from './ListLicensePackages'
import CreateLicensePackage from './CreateLicensePackage'
import EditLicensePackage from './EditLicensePackage'

const Pages = {
    ListLicensePackages: Object.assign(ListLicensePackages, ListLicensePackages),
    CreateLicensePackage: Object.assign(CreateLicensePackage, CreateLicensePackage),
    EditLicensePackage: Object.assign(EditLicensePackage, EditLicensePackage),
}

export default Pages