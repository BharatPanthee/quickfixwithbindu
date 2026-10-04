import { applyFilters } from "@wordpress/hooks";
import { toast } from "sonner";
import { Icon } from '../../components'

function License() {
    return (
        <>
            {applyFilters('yummy_bites_license_activation_placeholder', toast, Icon)}

        </>
    )
}

export default License;