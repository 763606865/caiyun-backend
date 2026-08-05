import ListCustomerRequirements from './ListCustomerRequirements'
import CreateCustomerRequirement from './CreateCustomerRequirement'
import EditCustomerRequirement from './EditCustomerRequirement'

const Pages = {
    ListCustomerRequirements: Object.assign(ListCustomerRequirements, ListCustomerRequirements),
    CreateCustomerRequirement: Object.assign(CreateCustomerRequirement, CreateCustomerRequirement),
    EditCustomerRequirement: Object.assign(EditCustomerRequirement, EditCustomerRequirement),
}

export default Pages