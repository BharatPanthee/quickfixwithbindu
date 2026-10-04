import { Icon } from "../../components"
import { __ } from "@wordpress/i18n"
import { mainOffer, offer2, offer3 } from "../../components/images"

const showOffers = (image, heading, description, link) => {
    return (
        <>
            <div className="image-wrapper">
                <img src={image} alt={__("Offer image")} />
            </div>
            <h2>{heading}</h2>
            <p> {description} </p>
            <a className="cw-button-btn btn-primary has-icon" target="_blank" href={link}> {__('Get it Now', 'yummy-bites')} <Icon icon="arrow" /> </a>
        </>
    )
}

function Offers() {

    const listItems = [
        {
            list: __('WP Delicious Pro', 'yummy-bites')
        },
        {
            list: __('Yummy Bites Pro', 'yummy-bites')
        },
        {
            list: __('60,000+ Recipe Keywords', 'yummy-bites')
        },
        {
            list: __('Delisho Pro', 'yummy-bites')
        },
        {
            list: __('Access to all future products', 'yummy-bites')
        },
    ]

    const offerList = [
        {
            image: offer2,
            heading: __('Grow Your Food Blog with WP Delicious Pro', 'yummy-bites'),
            description: __('WP Delicious Pro offers advanced features to create stunning recipes with ease, attract more readers, and boost engagement.', 'yummy-bites'),
            link: cw_dashboard?.pricing
        },
        {
            image: offer3,
            heading: __(`Skyrocket Your Food Blog's SEO!`, 'yummy-bites'),
            description: __('Unleash the Full Potential of Your Food Blog with Our Game-Changing Keyword Research file with 60,000+ Food and Recipe Keywords.', 'yummy-bites'),
            link: cw_dashboard?.keywords
        },
    ]


    return (
        <>
            <div className="offers-wrapper">
                <div className="featured-offers">
                    <div className="content-wrapper">
                        <h2>
                            {__('Get Lifetime Access to all WP Delicious Products', 'yummy-bites')}
                        </h2>
                        <p>
                            {__('Create a feature-rich and SEO-friendly food blog with our ultimate tools. With All Access Bundle, you get access to:', 'yummy-bites')}
                        </p>
                        <ul>
                            {listItems.map((item, index) => (
                                <li key={index}>
                                    <Icon icon="checkBox" />
                                    <span > {item.list}</span>
                                </li>
                            ))}
                        </ul>
                        <a className="cw-button-btn btn-primary has-icon" target="_blank" href={cw_dashboard?.bundle_pricing}> {__('Get it Now', 'yummy-bites')} <Icon icon="arrow" /> </a>

                    </div>

                    <div className="image-wrapper">
                        <img src={mainOffer} alt={__("Offer image")} />
                    </div>
                </div>
                <div className="offers">
                    {offerList.map((offer, index) => (
                        <div className="inner-wrapper" key={index}>
                            {showOffers(offer.image, offer.heading, offer.description, offer.link)}
                        </div>
                    ))}
                </div>
            </div>
        </>
    )
}

export default Offers