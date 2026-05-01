import { useState, useEffect } from '@wordpress/element';
import { PanelBody, Spinner, TabPanel } from '@wordpress/components';
import GeneralSettings from './components/GeneralSettings';
import SlotSettings from './components/SlotSettings';
import NotificationsSettings from './components/NotificationsSettings';
import SaveBar from './components/SaveBar';
import PaymentSettings from './components/PaymentSettings';

const App = () => {
	const [settings, setSettings] = useState({});
	const [loading, setLoading] = useState(true);

	const apiBase = window.CB_DATA.apiBase;
	const nonce = window.CB_DATA.nonce;

	useEffect(() => {
		fetch(apiBase, {
			headers: { 'X-WP-Nonce': nonce },
		})
		.then((res) => res.json())
		.then((data) => {
			setSettings(data);
			setLoading(false);
		});
	}, []);

	const updateSettings = (key, value) => {
		setSettings((prev) => ({
			...prev,
			[key]: value,
		}));
	};

	if (loading) return <Spinner />;

	return (
		<div className="admin-react-wrapper">
			<TabPanel
				className="health-visit-tabs"
				activeClass="is-active"
				tabs={[
					{ name: 'general', title: 'General', className: 'tab-general' },
					{ name: 'slots', title: 'Booking Slots' },
					{ name: 'notifications', title: 'Notifications' },
					{ name: 'payment', title: 'Payment Gateway' },
				]}
			>
				{(tab) => {
					switch (tab.name) {
						case 'general':
							return (
								<PanelBody title="General Settings" initialOpen={true}>
									<GeneralSettings settings={settings} update={updateSettings} />
								</PanelBody>
							);
						case 'slots':
							return (
								<PanelBody title="Slot Settings" initialOpen={true}>
									<SlotSettings settings={settings} update={updateSettings} />
								</PanelBody>
							);
						case 'notifications':
							return (
								<PanelBody title="Notification Settings" initialOpen={true}>
									<NotificationsSettings settings={settings} update={updateSettings} />
								</PanelBody>
							);

						case 'payment':
							return (
								<PanelBody title="Payment Settings" initialOpen={true}>
									<PaymentSettings settings={settings} update={updateSettings} />
								</PanelBody>
							);
						default:
							return null;
					}
				}}
			</TabPanel>

			<SaveBar settings={settings} apiBase={apiBase} nonce={nonce} />
		</div>
	);
};

export default App;
