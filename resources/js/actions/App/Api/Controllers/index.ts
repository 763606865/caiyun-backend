import AuthController from './AuthController'
import DeviceController from './DeviceController'
import ClientVersionController from './ClientVersionController'
import CustomerRequirementController from './CustomerRequirementController'
import LicenseLeaseController from './LicenseLeaseController'
import CmsController from './CmsController'
import FileController from './FileController'
import UserController from './UserController'

const Controllers = {
    AuthController: Object.assign(AuthController, AuthController),
    DeviceController: Object.assign(DeviceController, DeviceController),
    ClientVersionController: Object.assign(ClientVersionController, ClientVersionController),
    CustomerRequirementController: Object.assign(CustomerRequirementController, CustomerRequirementController),
    LicenseLeaseController: Object.assign(LicenseLeaseController, LicenseLeaseController),
    CmsController: Object.assign(CmsController, CmsController),
    FileController: Object.assign(FileController, FileController),
    UserController: Object.assign(UserController, UserController),
}

export default Controllers