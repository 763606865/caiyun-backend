import AuthController from './AuthController'
import DeviceController from './DeviceController'
import ClientVersionController from './ClientVersionController'
import LicenseLeaseController from './LicenseLeaseController'
import FileController from './FileController'
import UserController from './UserController'

const Controllers = {
    AuthController: Object.assign(AuthController, AuthController),
    DeviceController: Object.assign(DeviceController, DeviceController),
    ClientVersionController: Object.assign(ClientVersionController, ClientVersionController),
    LicenseLeaseController: Object.assign(LicenseLeaseController, LicenseLeaseController),
    FileController: Object.assign(FileController, FileController),
    UserController: Object.assign(UserController, UserController),
}

export default Controllers