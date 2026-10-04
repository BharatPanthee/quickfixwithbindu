import { Icon, Sidebar, Card, Heading } from "../../components";
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';    

const Homepage = () => {
    const cardLists = [
        {
            iconSvg: <Icon icon="site" />,
            heading: __('Site Identity', 'yummy-bites'),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.custom_logo
        },
        {
            iconSvg: <Icon icon="colorsetting" />,
            heading: __("Color Settings", 'yummy-bites'),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.colors
        },
        {
            iconSvg: <Icon icon="typographysetting" />,
            heading: __("Typography Settings"),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.typography
        },
        {
            iconSvg: <Icon icon="layoutsetting" />,
            heading: __("Layout Settings", 'yummy-bites'),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.layout
        },
        {
            iconSvg: <Icon icon="frontpagesetting" />,
            heading: __("Front Page Settings"),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.frontpage
        },
        {
            iconSvg: <Icon icon="generalsetting" />,
            heading: __("General Settings"),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.general
        },
        {
            iconSvg: <Icon icon="instagramsetting" />,
            heading: __("Instagram Settings", 'yummy-bites'),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.instagram
        },
        {
            iconSvg: <Icon icon="generalsetting" />,
            heading: __("Social Media"),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.social
        },
        {
            iconSvg: <Icon icon="footersetting" />,
            heading: __('Footer Settings', 'yummy-bites'),
            buttonText: __('Customize', 'yummy-bites'),
            buttonUrl: cw_dashboard.footer
        }
    ];

    const proSettings = [
        {
            heading: __('Header Layouts', 'yummy-bites'),
            para: __('Choose from different unique header layouts.', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            heading: __('Multiple Layouts', 'yummy-bites'),
            para: __('Choose layouts for blogs, banners, posts and more.', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            heading: __('Multiple Sidebar', 'yummy-bites'),
            para: __('Set different sidebars for posts and pages.', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            heading: __('Top Bar Settings', 'yummy-bites'),
            para: __('Show a notice or newsletter at the top.', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            para: __('Boost your website performance with ease.', 'yummy-bites'),
            heading: __('Performance Settings', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            para: __('Easily place javascript on your head,body,footer area.', 'yummy-bites'),
            heading: __('Build in Script Settings', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            para: __('Add additional content to enhance your homepage.', 'yummy-bites'),
            heading: __('Additional Frontpage Section', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            para: __('Safeguard your website’s content from being copied.', 'yummy-bites'),
            heading: __('Content Protection', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            para: __('Upload unlimited custom fonts to your website', 'yummy-bites'),
            heading: __('Custom Fonts', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            para: __('Display the latest recipes on your website.', 'yummy-bites'),
            heading: __('News Ticker', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            para: __('Add a customizable progress bar to your website.', 'yummy-bites'),
            heading: __('Progress Bar', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
        {
            para: __('Add unique and customizable widgets to your sidebar.', 'yummy-bites'),
            heading: __('Sidebar Blocks', 'yummy-bites'),
            buttonText: __('Learn More', 'yummy-bites'),
            buttonUrl: cw_dashboard?.get_pro
        },
    ];

    const sidebarSettings = [
        {
            heading: __('We Value Your Feedback!', 'yummy-bites'),
            icon: "star",
            para: __("Your review helps us improve and assists others in making informed choices. Share your thoughts today!", 'yummy-bites'),
            imageurl: <Icon icon="review" />,
            buttonText: __('Leave a Review', 'yummy-bites'),
            buttonUrl: cw_dashboard.review
        },
        {
            heading: __('Knowledge Base', 'yummy-bites'),
            para: __("Need help using our theme? Visit our well-organized Knowledge Base!", 'yummy-bites'),
            imageurl: <Icon icon="documentation" />,
            buttonText: __('Explore', 'yummy-bites'),
            buttonUrl: cw_dashboard.docmentation
        },
        {
            heading: __('Need Assistance? ', 'yummy-bites'),
            para: __("If you need help or have any questions, don't hesitate to contact our support team. We're here to assist you!", 'yummy-bites'),
            imageurl: <Icon icon="supportTwo" />,
            buttonText: __('Submit a Ticket', 'yummy-bites'),
            buttonUrl: cw_dashboard.support
        }
    ];

    const isProActivated = applyFilters( 'yummy_bites_is_pro_activated_pages', false ); 

    return (
        <>
            <div className="customizer-settings">
                <div className="cw-customizer">
                    <div className="video-section">
                        <div className="cw-settings">
                            <h2>{__('Yummy Bites Tutorial', 'yummy-bites')}</h2>
                        </div>
                        <iframe src="https://www.youtube.com/embed/BqvlcBIPi8M" title={__( 'How to Create a Food or Recipe Blog in 2023 with Yummy Bites Free and Pro WordPress Theme', 'yummy-bites' )} frameBorder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerPolicy="strict-origin-when-cross-origin" allowFullScreen></iframe>
                    </div>
                    <Heading
                        heading={__( 'Quick Customizer Settings', 'yummy-bites' )}
                        buttonText={__( 'Go To Customizer', 'yummy-bites' )}
                        buttonUrl={cw_dashboard?.customizer_url}
                        openInNewTab={true}
                    />
                    <Card
                        cardList={cardLists}
                        cardPlace='customizer'
                        cardCol='three-col'
                    />
                    {
                       !isProActivated ?
                        (
                            <>
                            <Heading
                                heading={__( 'More features with Pro version', 'yummy-bites' )}
                                buttonText={__( 'Go To Customizer', 'yummy-bites' )}
                                buttonUrl={cw_dashboard?.customizer_url}
                                openInNewTab={true}
                                />
                                <Card
                                    cardList={proSettings}
                                    cardPlace='cw-pro'
                                    cardCol='two-col'
                                />
                                <div className="cw-button">
                                    <a href={cw_dashboard?.get_pro} target="_blank" className="cw-button-btn primary-btn long-button">{__('Learn more about the Pro version', 'yummy-bites')}</a>
                                </div>
                            </>
                        )
                    : ''
                    }
                </div>
                <Sidebar sidebarSettings={sidebarSettings} openInNewTab={true} />
            </div>
        </>
    );
}

export default Homepage;