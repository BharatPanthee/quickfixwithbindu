import { useState } from 'react';
import { Icon, Tab } from '../components';
import FreePro from './FreePro';
import Homepage from "./Home";
import Offers from './Offers';
import License from './License';
import UsefulPlugins from './UsefulPlugins';
import FAQ from './FAQ';
import { applyFilters } from '@wordpress/hooks';
import StarterSites from './StarterSites';
import { __ } from '@wordpress/i18n';
import { Toaster } from 'sonner';

function Dashboard() {
    const [activeTabTitle, setActiveTabTitle] = useState('Home');

    const tabsData = [
        {
            title: __('Home', 'yummy-bites'),
            icon: <Icon icon="home" />,
            content: <Homepage />
        },
        {
            title: __('Starter Sites', 'yummy-bites'),
            icon: <Icon icon="globe" />,
            content: <StarterSites />
        },
        {
            title: __('Free vs Pro', 'yummy-bites'),
            icon: <Icon icon="freePro" />,
            content: <FreePro />
        },
        {
            title: __('Offers', 'yummy-bites'),
            icon: <Icon icon="offers" />,
            content: <Offers />
        },
        {
            title: __('FAQs', 'yummy-bites'),
            icon: <Icon icon="support" />,
            content: <FAQ />
        },
        {
            title: __('Useful Plugins', 'yummy-bites'),
            icon: <Icon icon="plugins" />,
            content: <UsefulPlugins />
        }
    ];

    let licenseTab = applyFilters('yummy_bites_pro_tabs_content', 
        tabsData,
        License,
        Icon
    )

    // Check if apply_filters is enabled
    const isProActivated = applyFilters('yummy_bites_is_pro_activated_pages', false); // Change this to the actual condition to enable filtering

    // Conditionally filter tabsData
    const filteredTabsData = isProActivated
        ? tabsData.filter(tab => (tab.title !== 'Free vs Pro'))
        : tabsData;

    const finalTabsData = filteredTabsData;

    const handleTabChange = (title) => {
        setActiveTabTitle(title);
    };

    return (
        <>
            <Tab
                tabsData={finalTabsData}
                onChange={handleTabChange}
                activeTabTitle={activeTabTitle}
            />
            <Toaster richColors closeButton />
        </>
    );
}

export default Dashboard;